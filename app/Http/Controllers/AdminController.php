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
            'members'             => Member::count(),
            'guests_today'        => Checkin::whereDate('checked_in_at', today())
                // HIER WAR VORHER member_type, DAS HAT GEPASST
                ->whereHas('registration', fn($q) => $q->where('member_type', 'guest'))
                ->count(),
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
            'members_csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);
    
        $storedPath = $request->file('members_csv')->store('imports');
    
        if (!$storedPath) {
            return redirect()
                ->route('admin.index')
                ->with('error', 'CSV konnte nicht sicher gespeichert werden.');
        }
    
        $fullPath = Storage::path($storedPath);
        $handle = fopen($fullPath, 'r');
    
        if (!$handle) {
            Storage::delete($storedPath);
    
            return redirect()
                ->route('admin.index')
                ->with('error', 'CSV konnte nicht geöffnet werden.');
        }
    
        try {
            $headers = fgetcsv($handle, 0, ';');
    
            if (!$headers) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV konnte nicht gelesen werden.');
            }
    
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    
            if (!in_array('Mitgliedsnummer', $headers, true)) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV-Format ungültig: Spalte "Mitgliedsnummer" fehlt.');
            }
    
            $imported = 0;
    
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (count($row) !== count($headers)) {
                    continue;
                }
    
                $data = array_combine($headers, $row);
    
                $memberNumber = trim($data['Mitgliedsnummer'] ?? '');
    
                if ($memberNumber === '') {
                    continue;
                }
    
                $betragOffen = trim((string)($data['Betrag offen'] ?? ''));
                $paymentStatus = $betragOffen ? 'paid' : 'open';
                
                $birthDate = $this->parseCsvDate($data['GebDatum'] ?? null);
                $exitDate = $this->parseCsvDate($data['Austrittsdatum'] ?? null);
                
                // NEU: Status-Spalte direkt auslesen
                $csvStatus = strtolower(trim((string)($data['Status'] ?? '')));
                
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
                
                // ↓ NEU: Registrierungen synchronisieren
                if ($membershipStatus === 'active' && $paymentStatus === 'paid') {
                Registration::where('member_number', $memberNumber)
                    ->where('access_status', 'red')
                    ->where(function ($q) {
                        $q->where('access_reason', 'like', '%inaktiv%')
                          ->orWhere('access_reason', 'like', '%Schnupperlimit%');
                    })
                    ->update([
                        'access_status' => 'green',
                        'access_reason' => 'Mitgliedschaft aktiv bezahlt',
                    ]);
                } elseif ($membershipStatus === 'active' && $paymentStatus === 'open') {
                    Registration::where('member_number', $memberNumber)
                        ->whereIn('access_status', ['red', 'green'])
                        ->where('access_reason', 'like', '%inaktiv%')
                        ->update([
                            'access_status' => 'orange',
                            'access_reason' => 'Beitrag offen',
                        ]);
                } elseif ($membershipStatus === 'inactive') {
                    Registration::where('member_number', $memberNumber)
                        ->where('access_status', '!=', 'red')
                        ->update([
                            'access_status' => 'red',
                            'access_reason' => 'Mitgliedschaft inaktiv',
                        ]);
                }
    
                $imported++;
            }
    
            if ($imported === 0) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV wurde gelesen, aber es konnten keine Datensätze importiert werden.');
            }
    
            return redirect()
                ->route('admin.index')
                ->with('success', "Mitgliederimport abgeschlossen: {$imported} Datensätze verarbeitet.");
        } finally {
            fclose($handle);
            Storage::delete($storedPath);
        }
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