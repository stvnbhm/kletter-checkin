<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Member;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
    
        $registrations = Registration::with('member', 'currentCheckin')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('first_name', 'like', "%$query%")
                        ->orWhere('last_name', 'like', "%$query%")
                        ->orWhere('member_number', 'like', "%$query%");
                });
            })
            ->orderByDesc('created_at')
            ->get()
            ->sortByDesc(fn($r) => $r->currentCheckin?->checked_in_at?->timestamp ?? 0);
    
        $stats = [
            'checkedInToday'     => Checkin::whereDate('checked_in_at', today())->count(),
            'guestsToday'        => Checkin::whereDate('checked_in_at', today())
                ->whereHas('registration', fn($q) => $q->where('member_type', 'guest'))
                ->count(),
            'membersToday'       => Checkin::whereDate('checked_in_at', today())
                ->whereHas('registration', fn($q) => $q->where('member_type', 'member'))
                ->count(),
            'totalRegistrations' => Registration::count(),
        ];
    
        return view('staff.index', compact('registrations', 'query', 'stats'));
    }

    public function checkin(Registration $registration)
    {
        $registration->load('currentCheckin', 'member'); // ← 'member' neu laden
    
        if ($registration->access_status === 'red') {
            return redirect()
                ->route('staff')
                ->with('error', 'Check-in verweigert: Kein Zutritt erlaubt.');
        }
    
        if ($registration->currentCheckin) {
            return redirect()
                ->route('staff')
                ->with('error', 'Diese Person ist bereits eingecheckt.');
        }
    
        // Schnuppergast-Limit
        if ($registration->member_type === 'guest' && $registration->trial_visits_count >= 1) {
            $hasActiveKulanz = $registration->manual_exception_until
                && $registration->manual_exception_until->isFuture();
    
            if (! $hasActiveKulanz) {
                return redirect()
                    ->route('staff')
                    ->with('error', 'Check-in verweigert: Schnuppergast hat den Erstbesuch bereits absolviert. Bitte Kulanz gewähren.');
            }
        }
    
        // ↓ NEU: Mitglied nicht in CSV → max. 2 Check-ins
        $isUnverifiedMember = $registration->member_type === 'member'
                              && $registration->member === null;
    
        if ($isUnverifiedMember) {
            $totalCheckins = Checkin::where('registration_id', $registration->id)->count();
    
            if ($totalCheckins >= 2) {
                // Sicherheitsnetz: Status auf red setzen falls noch nicht geschehen
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
    
                return redirect()
                    ->route('staff')
                    ->with('error', 'Check-in verweigert: Mitgliedsnummer nicht im Mitgliedersystem. Limit von 2 Besuchen ausgeschöpft.');
            }
        }
    
        Checkin::create([
            'registration_id' => $registration->id,
            'checked_in_at'   => now(),
        ]);
    
        $registration->increment('trial_visits_count');
    
        // ↓ NEU: Nach dem Check-in prüfen ob Limit jetzt erreicht
        if ($isUnverifiedMember) {
            $totalCheckins = Checkin::where('registration_id', $registration->id)->count();
    
            if ($totalCheckins >= 2) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
            }
        }
    
        return redirect()
            ->route('staff')
            ->withSuccess($registration->first_name . ' ' . $registration->last_name . ' wurde erfolgreich eingecheckt.');
    }

    public function importMembers(Request $request)
    {
        $request->validate([
            'members_csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $storedPath = $request->file('members_csv')->store('imports');

        if (!$storedPath) {
            return redirect()
                ->route('staff')
                ->with('error', 'CSV konnte nicht sicher gespeichert werden.');
        }

        $fullPath = Storage::path($storedPath);
        $handle = fopen($fullPath, 'r');

        if (!$handle) {
            Storage::delete($storedPath);

            return redirect()
                ->route('staff')
                ->with('error', 'CSV konnte nicht geöffnet werden.');
        }

        try {
            $headers = fgetcsv($handle, 0, ';');

            if (!$headers) {
                return redirect()
                    ->route('staff')
                    ->with('error', 'CSV konnte nicht gelesen werden.');
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

            if (!in_array('Mitgliedsnummer', $headers, true)) {
                return redirect()
                    ->route('staff')
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

                $betragOffen = trim((string) ($data['Betrag_offen'] ?? ''));
                $paymentStatus = $betragOffen === '' ? 'paid' : 'open';

                $birthDate = $this->parseCsvDate($data['Geb_Datum'] ?? null);
                $exitDate  = $this->parseCsvDate($data['Austrittsdatum'] ?? null);

                $membershipStatus = 'active';
                if ($exitDate && $exitDate->isPast()) {
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

                $imported++;
            }

            if ($imported === 0) {
                return redirect()
                    ->route('staff')
                    ->with('error', 'CSV wurde gelesen, aber es konnten keine Datensätze importiert werden.');
            }

            return redirect()
                ->route('staff')
                ->with('success', "Mitgliederimport abgeschlossen: {$imported} Datensätze verarbeitet.");
        } finally {
            fclose($handle);
            Storage::delete($storedPath);
        }
    }

    public function grantKulanz(Request $request, Registration $registration)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
    
        $reason = strip_tags($request->reason); // ← NEU
    
        $registration->update([
            'manual_exception_reason' => $reason,
            'manual_exception_until'  => now()->endOfDay(),
            'access_status'           => 'orange',
            'access_reason'           => 'Kulanz: ' . $reason,
        ]);
    
        return redirect()
            ->route('staff')
            ->with('success', 'Kulanz gewährt für ' . e($registration->first_name) . '!');
    }
    
        /**
     * Kulanz erteilen UND sofort Check-in durchführen (für orange-Status).
     */
    public function kulanzCheckin(Request $request, Registration $registration): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
    
        $reason = strip_tags($request->reason);
    
        $registration->load('currentCheckin');
        if ($registration->currentCheckin) {
            return redirect()
                ->route('staff')
                ->with('error', e($registration->first_name) . ' ist bereits eingecheckt.');
        }
    
        // 1. Kulanz erteilen
        $registration->update([
            'manual_exception_reason' => $reason,
            'manual_exception_until'  => now()->endOfDay(),
            'access_status'           => 'orange',
            'access_reason'           => 'Kulanz: ' . $reason,
        ]);
    
        // 2. Check-in sofort durchführen
        Checkin::create([
            'registration_id' => $registration->id,
            'checked_in_at'   => now(),
        ]);
    
        $registration->increment('trial_visits_count');
    
        return redirect()
            ->route('staff')
            ->with('success', '✓ Kulanz erteilt & ' . e($registration->first_name) . ' ' . e($registration->last_name) . ' eingecheckt.');
    }
    
    public function checkoutAll(): RedirectResponse
    {
        $now = now();
    
        $openCheckins = Checkin::whereNull('checked_out_at')->get();
        $count = $openCheckins->count();
    
        foreach ($openCheckins as $checkin) {
            $checkin->update(['checked_out_at' => $now]);
    
            // checked_in_at auf der Registration zurücksetzen
            Registration::where('id', $checkin->registration_id)
                ->update(['checked_in_at' => null]);
        }
    
        return redirect()->route('staff')
            ->with('success', '✓ ' . $count . ' ' . ($count === 1 ? 'Person' : 'Personen') . ' ausgecheckt.');
    }

    public function confirmParentConsent(Registration $registration)
    {
        $registration->update([
            'parent_consent_received'    => true,
            'parent_consent_received_at' => now(),
            'needs_parent_consent'       => false,
        ]);

        return redirect()
            ->route('staff')
            ->with('success', 'Einverständniserklärung wurde bestätigt.');
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