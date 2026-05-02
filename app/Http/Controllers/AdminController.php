<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Member;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ── Dashboard ──────────────────────────────────────────
    public function index()
    {
        $registrations = Registration::withCount('checkins')
            ->orderByDesc('created_at')
            ->paginate(30);

        // Hallenauslastung: Checkins pro Tag (letzte 30 Tage)
        $chartData = Checkin::select(
                DB::raw('DATE(checked_in_at) as day'),
                DB::raw('COUNT(*) as total')
            )
            ->where('checked_in_at', '>=', Carbon::now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Auffüllen: auch Tage ohne Check-ins erscheinen im Chart
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
            'members'             => Member::where('membership_status', 'active')->count(),           // ✅ Members-Tabelle
            'guests_today'        => Checkin::whereDate('checked_in_at', today())
                ->whereHas('registration', fn($q) => $q->where('member_type', 'guest'))
                ->count(),
            'inactive_members'    => Member::where('membership_status', 'inactive')->count(),       // ✅ Members-Tabelle!
        ];

        return view('admin.index', compact('registrations', 'labels', 'values', 'stats'));
    }

    // ── Registrierung löschen ──────────────────────────────
    public function destroyRegistration(Registration $registration)
    {
        // Checkins mitlöschen
        $registration->checkins()->delete();
        $registration->delete();

        return back()->with('success', 'Registrierung wurde gelöscht.');
    }

    // ── Mitglieder CSV-Import ──────────────────────────────

    public function importMembers(Request $request)
    {
        $request->validate([
            'members_csv' => ['nullable', 'file', 'mimes:csv,txt'],
            'confirm_missing_count' => ['nullable', 'integer'],
            'stored_csv_path' => ['nullable', 'string'],
        ]);
    
        $storedPath = null;
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
        $handle = fopen($fullPath, 'r');
    
        if (!$handle) {
            if ($deleteStoredFile && Storage::exists($storedPath)) {
                Storage::delete($storedPath);
            }
    
            return redirect()
                ->route('admin.index')
                ->with('error', 'CSV konnte nicht geöffnet werden.');
        }
    
        try {
            $headers = fgetcsv($handle, 0, ';');
    
            if (!$headers) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV konnte nicht gelesen werden.')
                    ->with('stored_csv_path', $storedPath);
            }
    
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    
            if (!in_array('Mitgliedsnummer', $headers, true)) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV-Format ungültig: Spalte "Mitgliedsnummer" fehlt.')
                    ->with('stored_csv_path', $storedPath);
            }
    
            $imported = 0;
            $importedMemberNumbers = [];
    
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (count($row) !== count($headers)) {
                    continue;
                }
    
                $data = array_combine($headers, $row);
    
                $memberNumber = trim($data['Mitgliedsnummer'] ?? '');
    
                if ($memberNumber === '') {
                    continue;
                }
    
                $importedMemberNumbers[] = $memberNumber;
    
                $betragOffen = (float) str_replace(',', '.', trim((string) ($data['Betrag offen'] ?? '0')));
                $paymentStatus = $betragOffen > 0 ? 'overdue' : 'paid';
    
                $birthDate = $this->parseCsvDate($data['Geburtsdatum'] ?? null);
                $exitDate = $this->parseCsvDate($data['Austrittsdatum'] ?? null);
    
                $csvStatus = strtolower(trim((string) ($data['Status'] ?? '')));
                $inactiveStatuses = ['gelöscht', 'ausgetreten', 'gesperrt', 'inaktiv', 'gekündigt'];
    
                $membershipStatus = 'active';
                if (in_array($csvStatus, $inactiveStatuses, true)) {
                    $membershipStatus = 'inactive';
                } elseif ($exitDate && $exitDate->isPast()) {
                    $membershipStatus = 'inactive';
                }
    
                Member::updateOrCreate(
                    ['member_number' => $memberNumber],
                    [
                        'first_name' => trim((string) ($data['Vorname'] ?? '')),
                        'last_name' => trim((string) ($data['Nachname'] ?? '')),
                        'email' => $this->nullIfEmpty($data['Email'] ?? null),
                        'birth_date' => $birthDate?->format('Y-m-d'),
                        'membership_status' => $membershipStatus,
                        'payment_status' => $paymentStatus,
                        'last_imported_at' => now(),
                    ]
                );
    
                Registration::where('member_type', 'guest')
                    ->whereRaw('LOWER(last_name) = ?', [strtolower(trim((string) ($data['Nachname'] ?? '')))])
                    ->where('birth_date', $birthDate?->format('Y-m-d'))
                    ->update([
                        'member_type' => 'member',
                        'member_number' => $memberNumber,
                    ]);
    
                Registration::where('member_number', $memberNumber)->update([
                    'payment_status' => $paymentStatus,
                ]);
    
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
    
                return redirect()
                    ->route('admin.index')
                    ->with('warning', "Import abgebrochen: {$missingCount} bestehende Mitglieder fehlen in der CSV. Bitte Import erneut starten und diese Anzahl explizit bestätigen.")
                    ->with('confirm_missing_count_required', $missingCount)
                    ->with('stored_csv_path', $storedPath);
            }
    
            if ($missingCount > 0) {
                Member::whereIn('member_number', $missingMemberNumbers)->update([
                    'membership_status' => 'inactive',
                    'last_imported_at' => now(),
                ]);
    
                Registration::whereIn('member_number', $missingMemberNumbers)->update([
                    'payment_status' => 'overdue',
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedschaft inaktiv',
                ]);
            }
    
            return redirect()
                ->route('admin.index')
                ->with('success', "Mitgliederimport abgeschlossen: {$imported} Datensätze verarbeitet.");
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
        $ids = Registration::where('membership_status', 'inactive')->pluck('id');
        Checkin::whereIn('registration_id', $ids)->delete();
        Registration::whereIn('id', $ids)->delete();
        return redirect()->route('admin.index')->with('success', count($ids) . ' inaktive Mitglieder gelöscht.');
    }

    // ── Checkins CSV-Export ────────────────────────────────
    public function exportCheckins(Request $request)
    {
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $to   = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : Carbon::now()->endOfDay();
    
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
                    $reg->first_name  ?? '',
                    $reg->last_name   ?? '',
                    $reg->birth_date  ? Carbon::parse($reg->birth_date)->format('d.m.Y') : '',
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

    private function nullIfEmpty(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}