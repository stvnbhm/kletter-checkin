<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        $registrations = Registration::with('member', 'currentCheckin', 'checkins')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('first_name', 'like', '%' . $query . '%')
                        ->orWhere('last_name', 'like', '%' . $query . '%')
                        ->orWhere('member_number', 'like', '%' . $query . '%');
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

    public function checkin(Registration $registration): RedirectResponse
    {
        $registration->load('currentCheckin', 'member');

        // Bereits eingecheckt?
        if ($registration->currentCheckin) {
            return redirect()->route('staff')
                ->with('error', $registration->first_name . ' ' . $registration->last_name . ' ist bereits eingecheckt.');
        }

        $visits = $registration->trial_visits_count ?? 0;

        // ── HART GESPERRT ──────────────────────────────────────────────
        if ($registration->access_status === 'red') {
            return redirect()->route('staff')
                ->with('error', 'Check-in verweigert – ' . $registration->first_name . ' hat keinen Zutritt (Status: rot).');
        }

        // Schnuppergast ab Besuch 4 (3 bereits absolviert) → gesperrt
        if ($registration->member_type === 'guest' && $visits >= 3) {
            return redirect()->route('staff')
                ->with('error', 'Check-in verweigert – Schnupperlimit (3 Besuche) ausgeschöpft.');
        }

        // ── MODAL ERFORDERLICH ─────────────────────────────────────────
        // Orange → modal-pflicht
        // Schnuppergast ab Besuch 2 (visits >= 1) → modal-pflicht
        $requiresModal = $registration->access_status === 'orange'
                      || ($registration->member_type === 'guest' && $visits >= 1);

        $reason = strip_tags(request('reason', ''));

        if ($requiresModal && $reason === '') {
            return redirect()->route('staff')
                ->with('error', 'Check-in verweigert – Bestätigung mit Grund erforderlich.');
        }

        // ── UNVERIFIED MEMBER: max. 3 Check-ins ───────────────────────
        $isUnverifiedMember = $registration->member_type === 'member'
                           && $registration->member === null;

        if ($isUnverifiedMember) {
            $totalCheckins = Checkin::where('registration_id', $registration->id)->count();
            if ($totalCheckins >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
                return redirect()->route('staff')
                    ->with('error', 'Check-in verweigert – Mitgliedsnummer nicht gefunden. Limit von 3 Besuchen ausgeschöpft.');
            }
        }

        // ── CHECK-IN DURCHFÜHREN ───────────────────────────────────────
                // ── CHECK-IN DURCHFÜHREN ───────────────────────────────────────
        if ($reason !== '') {
            $registration->update([
                'manual_exception_reason' => $reason,
            ]);
        }

        Checkin::create([
            'registration_id' => $registration->id,
            'checked_in_at'   => now(),
        ]);

        $registration->increment('trial_visits_count');
        $registration->refresh();

        // Nach Check-in Status-Update Schnuppergast
        if ($registration->member_type === 'guest') {
            if ($registration->trial_visits_count >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Schnupperlimit ausgeschöpft (3/3)',
                ]);
            } else {
                $registration->update([
                    'access_status' => 'orange',
                    'access_reason' => 'Schnupperklettern bereits absolviert am ' . now()->format('d.m.Y'),
                ]);
            }
        }


        // Unverified Member nach 3 Besuchen → rot
        if ($isUnverifiedMember) {
            $totalCheckins = Checkin::where('registration_id', $registration->id)->count();
            if ($totalCheckins >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
            }
        }

        return redirect()->route('staff')
            ->with('success', '✓ ' . $registration->first_name . ' ' . $registration->last_name . ' eingecheckt.');
    }

    public function checkoutAll(): RedirectResponse
    {
        $now = now();
        $openCheckins = Checkin::whereNull('checked_out_at')->get();
        $count = $openCheckins->count();

        foreach ($openCheckins as $checkin) {
            $checkin->update(['checked_out_at' => $now]);

            $registration = Registration::with('member')->find($checkin->registration_id);
            if (!$registration) continue;

            $registration->update(['checked_in_at' => null]);

            if ($registration->member_type === 'guest' && $registration->trial_visits_count >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Schnupperlimit ausgeschöpft (3/3)',
                ]);
            } elseif ($registration->member_type === 'guest' && $registration->trial_visits_count >= 1) {
                $registration->update([
                    'access_reason' => 'Schnupperklettern bereits absolviert am '
                        . $checkin->checked_in_at->format('d.m.Y'),
                ]);
            }

            $isUnverifiedMember = $registration->member_type === 'member'
                                && $registration->member === null;

            if ($isUnverifiedMember && $registration->trial_visits_count >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
            }
        }

        return redirect()->route('staff')
            ->with('success', '✓ ' . $count . ' ' . ($count === 1 ? 'Person' : 'Personen') . ' ausgecheckt.');
    }

    public function confirmParentConsent(Registration $registration): RedirectResponse
    {
        $registration->update([
            'parent_consent_received'    => true,
            'parent_consent_received_at' => now(),
            'needs_parent_consent'       => false,
        ]);

        return redirect()->route('staff')
            ->with('success', 'Einverständniserklärung wurde bestätigt.');
    }

    public function importMembers(Request $request): RedirectResponse
    {
        return redirect()->route('staff')
            ->with('error', 'Import bitte über den Admin-Bereich durchführen.');
    }

    private function parseCsvDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        try {
            return Carbon::createFromFormat('d.m.Y', $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function nullIfEmpty(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
