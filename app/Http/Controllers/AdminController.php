<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Member;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    // ── Dashboard ──────────────────────────────────────────
    public function index(Request $request)
    {
        $query        = $request->input('q');
        $statusFilter = $request->input('status');

        $registrations = Registration::withCount('checkins')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('first_name',    'like', '%' . $query . '%')
                        ->orWhere('last_name',   'like', '%' . $query . '%')
                        ->orWhere('member_number','like', '%' . $query . '%')
                        ->orWhere('notes',       'like', '%' . $query . '%');
                });
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                if ($statusFilter === 'guest') {
                    $q->where('member_type', 'guest');
                } else {
                    $q->where('access_status', $statusFilter);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString(); // ← Filter bei Pagination beibehalten!

        $chartData = Checkin::select(
                DB::raw('DATE(checked_in_at) as day'),
                DB::raw('COUNT(*) as total')
            )
            ->where('checked_in_at', '>=', Carbon::now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $values = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($day)->format('d.m');
            $values[] = $chartData->get($day)->total ?? 0;
        }

        $stats = [
            'total_registrations' => Registration::count(),
            'checked_in_today'    => Checkin::whereDate('checked_in_at', today())->count(),
            'members'             => Member::where('membership_status', 'active')->count(),
            'guests_today'        => Checkin::whereDate('checked_in_at', today())
                ->whereHas('registration', fn($q) => $q->where('member_type', 'guest'))
                ->count(),
            'inactive_members'    => Member::where('membership_status', 'inactive')->count(),
        ];

        return view('admin.index', compact(
            'registrations', 'labels', 'values', 'stats', 'query', 'statusFilter'
        ));
    }

    // ── Registrierung löschen ──────────────────────────────
    public function destroyRegistration(Registration $registration)
    {
        $registration->checkins()->delete();
        $registration->delete();

        return back()->with('success', 'Registrierung wurde gelöscht.');
    }

    public function updateRegistrationNotes(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        $raw = $validated['notes'] ?? '';
        $trimmed = is_string($raw) ? trim($raw) : '';
        $registration->update(['notes' => $trimmed === '' ? null : $trimmed]);

        $queryParams = array_filter(
            $request->only(['q', 'status', 'page']),
            fn ($v) => $v !== null && $v !== ''
        );

        return redirect()->route('admin.index', $queryParams)
            ->with('success', 'Notiz gespeichert.');
    }

    // ── Mitglieder CSV-Import ──────────────────────────────

    public function importMembers(Request $request)
    {
        $request->validate([
            'members_csv'           => ['nullable', 'file', 'mimes:csv,txt'],
            'confirm_missing_count' => ['nullable', 'integer'],
            'stored_csv_path'       => ['nullable', 'string'],
        ]);

        $storedPath      = null;
        $deleteStoredFile = false;

        if ($request->hasFile('members_csv')) {
            $storedPath = $request->file('members_csv')->store('imports');
            $deleteStoredFile = true;

            if (!$storedPath) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV konnte nicht sicher gespeichert werden.');
            }
        } elseif ($request->filled('stored_csv_path')) {
            $storedPath = $request->input('stored_csv_path');

            if (!Storage::exists($storedPath)) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'Die zwischengespeicherte CSV wurde nicht gefunden. Bitte Datei erneut auswählen.');
            }

            $deleteStoredFile = true;
        } else {
            return redirect()
                ->route('admin.index')
                ->with('error', 'Bitte CSV-Datei auswählen.');
        }

        $fullPath = Storage::path($storedPath);
        $handle   = fopen($fullPath, 'r');

        if (!$handle) {
            if ($deleteStoredFile && Storage::exists($storedPath)) {
                Storage::delete($storedPath);
            }

            return redirect()
                ->route('admin.index')
                ->with('error', 'CSV konnte nicht geöffnet werden.');
        }

        try {
            // ── 1. Delimiter automatisch erkennen ─────────
            $delimiter = $this->detectCsvDelimiter($handle);

            // ── 2. Header-Zeile lesen & bereinigen ────────
            $headers = fgetcsv($handle, 0, $delimiter);

            if (!$headers) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV konnte nicht gelesen werden.')
                    ->with('stored_csv_path', $storedPath);
            }

            // BOM entfernen + alle Header trimmen
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers    = array_map(fn($h) => trim((string) $h), $headers);

            if (!in_array('Mitgliedsnummer', $headers, true)) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV-Format ungültig: Spalte "Mitgliedsnummer" fehlt.')
                    ->with('stored_csv_path', $storedPath);
            }

            // ── 3. Zeilen einlesen ─────────────────────────
            $imported              = 0;
            $skippedRows           = 0;
            $importedMemberNumbers = [];

            $inactiveStatuses = [
                'gelöscht', 'geloescht',
                'ausgetreten',
                'gesperrt',
                'inaktiv',
                'gekündigt', 'gekuendigt',
            ];

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Leerzeilen überspringen
                if ($this->isEmptyCsvRow($row)) {
                    continue;
                }

                // Zeilen mit falscher Spaltenanzahl überspringen & mitzählen
                if (count($row) !== count($headers)) {
                    $skippedRows++;
                    continue;
                }

                $data = array_combine($headers, $row);

                if ($data === false) {
                    $skippedRows++;
                    continue;
                }

                $memberNumber = trim((string) ($data['Mitgliedsnummer'] ?? ''));

                if ($memberNumber === '') {
                    continue;
                }

                $importedMemberNumbers[] = $memberNumber;

                $paymentStatus = $this->parseCsvAmount($data['Betrag offen'] ?? null) > 0
                    ? 'overdue'
                    : 'paid';

                $birthDate = $this->parseCsvDate($data['Geburtsdatum'] ?? null);
                $exitDate  = $this->parseCsvDate($data['Austrittsdatum'] ?? null);

                $csvStatus = mb_strtolower(trim((string) ($data['Status'] ?? '')));

                $membershipStatus = 'active';
                if (in_array($csvStatus, $inactiveStatuses, true)) {
                    $membershipStatus = 'inactive';
                } elseif ($exitDate && $exitDate->isPast()) {
                    $membershipStatus = 'inactive';
                }

                Member::updateOrCreate(
                    ['member_number' => $memberNumber],
                    [
                        'first_name'        => trim((string) ($data['Vorname'] ?? '')),
                        'last_name'         => trim((string) ($data['Nachname'] ?? '')),
                        'email'             => $this->nullIfEmpty($data['Email'] ?? null),
                        'birth_date'        => $birthDate?->format('Y-m-d'),
                        'membership_status' => $membershipStatus,
                        'payment_status'    => $paymentStatus,
                        'last_imported_at'  => now(),
                    ]
                );

                // Gast → Mitglied upgraden falls Name + Geburtsdatum passen
                Registration::where('member_type', 'guest')
                    ->whereRaw('LOWER(last_name) = ?', [
                        mb_strtolower(trim((string) ($data['Nachname'] ?? ''))),
                    ])
                    ->where('birth_date', $birthDate?->format('Y-m-d'))
                    ->update([
                        'member_type'   => 'member',
                        'member_number' => $memberNumber,
                    ]);

                // Zahlungsstatus synchronisieren
                Registration::where('member_number', $memberNumber)->update([
                    'payment_status' => $paymentStatus,
                ]);

                // Zugangsstatus synchronisieren
                if ($membershipStatus === 'inactive') {
                    Registration::where('member_number', $memberNumber)->update([
                        'access_status' => 'red',
                        'access_reason' => 'Mitgliedschaft inaktiv',
                    ]);
                } elseif ($paymentStatus === 'overdue') {
                    Registration::where('member_number', $memberNumber)->update([
                        'access_status' => 'orange',
                        'access_reason' => 'Beitrag offen',
                    ]);
                } else {
                    Registration::where('member_number', $memberNumber)->update([
                        'access_status' => 'green',
                        'access_reason' => 'Mitgliedschaft aktiv & bezahlt',
                    ]);
                }

                $imported++;
            }

            if ($imported === 0) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV wurde gelesen, aber es konnten keine Datensätze importiert werden.')
                    ->with('stored_csv_path', $storedPath);
            }

            $importedMemberNumbers = array_values(array_unique($importedMemberNumbers));

            $missingMemberNumbers = Member::query()
                ->whereNotIn('member_number', $importedMemberNumbers)
                ->pluck('member_number');

            $missingCount = $missingMemberNumbers->count();

            if ($missingCount > 0 && (int) $request->input('confirm_missing_count') !== $missingCount) {
                $deleteStoredFile = false;

                $skippedHint = $skippedRows > 0
                    ? " ({$skippedRows} Zeilen wegen falscher Spaltenanzahl übersprungen)"
                    : '';

                return redirect()
                    ->route('admin.index')
                    ->with('warning', "Import abgebrochen: {$missingCount} bestehende Mitglieder fehlen in der CSV. Bitte Import erneut starten und diese Anzahl explizit bestätigen.{$skippedHint}")
                    ->with('confirm_missing_count_required', $missingCount)
                    ->with('stored_csv_path', $storedPath);
            }

            if ($missingCount > 0) {
                Member::whereIn('member_number', $missingMemberNumbers)->update([
                    'membership_status' => 'inactive',
                    'last_imported_at'  => now(),
                ]);

                Registration::whereIn('member_number', $missingMemberNumbers)->update([
                    'payment_status' => 'overdue',
                    'access_status'  => 'red',
                    'access_reason'  => 'Mitgliedschaft inaktiv',
                ]);
            }

            $successMsg = "Mitgliederimport abgeschlossen: {$imported} Datensätze verarbeitet.";

            if ($skippedRows > 0) {
                $successMsg .= " ({$skippedRows} Zeile(n) mit falscher Spaltenanzahl übersprungen)";
            }

            return redirect()
                ->route('admin.index')
                ->with('success', $successMsg);

        } finally {
            fclose($handle);

            if ($deleteStoredFile && $storedPath && Storage::exists($storedPath)) {
                Storage::delete($storedPath);
            }
        }
    }

    // ── Inaktive Mitglieder löschen ────────────────────────
    public function deleteInactiveMembers(): RedirectResponse
    {
        $memberNumbers = Member::where('membership_status', 'inactive')
            ->pluck('member_number');

        $registrationIds = Registration::whereIn('member_number', $memberNumbers)
            ->pluck('id');

        Checkin::whereIn('registration_id', $registrationIds)->delete();
        Registration::whereIn('id', $registrationIds)->delete();
        Member::whereIn('member_number', $memberNumbers)->delete();

        return redirect()
            ->route('admin.index')
            ->with('success', $memberNumbers->count() . ' inaktive Mitglieder gelöscht.');
    }

    // ── Checkins CSV-Export ────────────────────────────────
    public function exportCheckins(Request $request)
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $checkins = Checkin::with('registration')
            ->whereBetween('checked_in_at', [$from, $to])
            ->orderBy('checked_in_at')
            ->get();

        $filename = 'checkins_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($checkins) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Check-in ID',
                'Vorname',
                'Nachname',
                'Geburtsdatum',
                'Mitgliedstyp',
                'Mitgliedsnummer',
                'Check-in Zeit',
                'Ampelstatus',
                'Zugangsstatus-Grund',
                'Schnupperbesuche',
                'Aufsicht erforderlich',
            ], ';');

            foreach ($checkins as $c) {
                $reg = $c->registration;

                fputcsv($handle, [
                    $c->id,
                    $reg->first_name   ?? '',
                    $reg->last_name    ?? '',
                    $reg->birth_date   ? Carbon::parse($reg->birth_date)->format('d.m.Y') : '',
                    $reg->member_type === 'member' ? 'Mitglied' : 'Gast',
                    $reg->member_number ?? '',
                    Carbon::parse($c->checked_in_at)->format('d.m.Y H:i'),
                    $reg->access_status ?? '',
                    $reg->access_reason ?? '',
                    $reg->member_type === 'guest' ? ($reg->trial_visits_count ?? 0) : '',
                    $reg->needs_supervision ? 'Ja' : 'Nein',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Private Helpers ────────────────────────────────────

    /**
     * Liest die erste Zeile der Datei und wählt den häufigsten
     * Kandidaten (;  ,  Tab) als Delimiter. Zeiger wird zurückgesetzt.
     *
     * @param  resource  $handle
     */
    private function detectCsvDelimiter($handle): string
    {
        rewind($handle);

        $firstLine = fgets($handle);
        rewind($handle);

        if ($firstLine === false || trim($firstLine) === '') {
            return ';';
        }

        // BOM entfernen, damit er nicht in die Zählung einfließt
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        $candidates = [';', ',', "\t"];
        $counts     = [];

        foreach ($candidates as $candidate) {
            $counts[$candidate] = substr_count($firstLine, $candidate);
        }

        arsort($counts);
        $best = array_key_first($counts);

        // Nur übernehmen wenn mindestens 1 Vorkommen
        return ($counts[$best] ?? 0) > 0 ? $best : ';';
    }

    /**
     * Gibt true zurück wenn eine CSV-Zeile ausschließlich leere Werte enthält.
     */
    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Parst Geldbeträge robust:
     * - "1.234,56" (österreichisch)  → 1234.56
     * - "1234.56"  (englisch)        → 1234.56
     * - "15,00"                      → 15.00
     */
    private function parseCsvAmount(?string $value): float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0.0;
        }

        // Whitespace & geschütztes Leerzeichen entfernen
        $value = preg_replace('/[\s\x{00A0}]+/u', '', $value);

        // Österreichisch: Punkt als Tausender, Komma als Dezimal → "1.234,56"
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // Nur Komma → Dezimalkomma
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * Parst TT.MM.JJJJ → Carbon. Gibt null zurück bei leerem/ungültigem Wert.
     */
    private function parseCsvDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d.m.Y', $value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Gibt null zurück wenn der Wert nach trim() leer ist.
     */
    private function nullIfEmpty(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
