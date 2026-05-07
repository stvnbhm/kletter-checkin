<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Checkin;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function create(Request $request)
    {
        $request->session()->put('register_form_started_at', now()->timestamp);
        return view('register');
    }

    public function store(Request $request)
    {
        // Honeypot-Spam-Schutz
        if (filled($request->input('website')) || filled($request->input('fax_number'))) {
            \Log::warning('Spam blockiert: Honeypot-Feld ausgefüllt', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            throw ValidationException::withMessages([
                'first_name' => 'Die Registrierung konnte nicht verarbeitet werden. Bitte versuche es erneut.',
            ]);
        }

        $formStartedAt = (int) $request->session()->get('register_form_started_at', 0);
        $secondsTaken  = now()->timestamp - $formStartedAt;
        if ($formStartedAt > 0 && $secondsTaken < 3) {
            \Log::warning('Spam blockiert: Formular zu schnell abgesendet', [
                'ip'           => $request->ip(),
                'ua'           => $request->userAgent(),
                'secondstaken' => $secondsTaken,
            ]);
            throw ValidationException::withMessages([
                'first_name' => 'Die Registrierung konnte nicht verarbeitet werden. Bitte versuche es erneut.',
            ]);
        }

        $validated = $request->validate([
            'first_name'            => 'required|string|max:255',
            'last_name'             => 'required|string|max:255',
            'birth_date'            => 'required|date|after_or_equal:1900-01-01|before_or_equal:today',
            'email'                 => 'nullable|email|max:255',
            'member_type'           => 'required|in:member,guest',
            'member_number'         => [
                'required_if:member_type,member',
                'nullable',
                'string',
                'regex:/^\d{2}-\d{5}$/',
            ],
            'waiver_accepted'       => 'required|accepted',
            'rules_accepted'        => 'required|accepted',
            'supervision_confirmed' => 'nullable|boolean',
            'hp_time'               => 'required|integer',
            'website'               => 'nullable|max:0',
            'fax_number'            => 'nullable|max:0',
        ], [
            'birth_date.required'  => 'Das Geburtsdatum ist erforderlich, um doppelte Registrierungen zu vermeiden.',
            'member_number.regex'  => 'Die Mitgliedsnummer muss im Format XX-XXXXX eingegeben werden (z.B. 12-34567).',
        ]);

        // Freitextfelder bereinigen
        $validated['first_name']    = strip_tags($validated['first_name']);
        $validated['last_name']     = strip_tags($validated['last_name']);
        $validated['member_number'] = isset($validated['member_number'])
            ? strip_tags($validated['member_number'])
            : null;

        $birthDate          = Carbon::parse($validated['birth_date']);
        $age                = $birthDate->age;
        $needsSupervision   = $age < 14;
        $needsParentConsent = $age >= 14 && $age < 18;

        if ($needsSupervision && !$request->boolean('supervision_confirmed')) {
            throw ValidationException::withMessages([
                'supervision_confirmed' => 'Für Kinder unter 14 Jahren muss bestätigt werden, dass Klettern nur unter Aufsicht erfolgt.',
            ]);
        }

        // Mitglieds-Verifikation gegen Mitgliederliste
        $member = null;
        if ($validated['member_type'] === 'member') {
            $member = DB::table('members')
                ->where('member_number', $validated['member_number'])
                ->first();

            $lastNameInput = strtolower(trim($validated['last_name']));
            $birthInput    = Carbon::parse($validated['birth_date'])->toDateString();

            if ($member) {
                $lastNameDb = strtolower(trim($member->last_name ?? ''));
                $birthDb    = $member->birth_date
                    ? Carbon::parse($member->birth_date)->toDateString()
                    : null;

                if ($lastNameInput !== $lastNameDb || $birthInput !== $birthDb) {
                    throw ValidationException::withMessages([
                        'member_number' => 'Die Mitgliedsnummer stimmt nicht mit den angegebenen Daten (Nachname + Geburtsdatum) überein. Bitte prüfen!',
                    ]);
                }
            } else {
                $nameBirthMatch = DB::table('members')
                    ->whereRaw('LOWER(last_name) = ?', [$lastNameInput])
                    ->whereDate('birth_date', $birthInput)
                    ->exists();

                if ($nameBirthMatch) {
                    throw ValidationException::withMessages([
                        'member_number' => 'Die Mitgliedsnummer stimmt nicht mit den angegebenen Daten (Nachname + Geburtsdatum) überein. Bitte prüfen!',
                    ]);
                }
            }
        }

        // Duplikat-Suche
        $query = Registration::query();
        if ($validated['member_type'] === 'member' && !empty($validated['member_number'])) {
            $query->where('member_number', $validated['member_number']);
        } else {
            $query->whereRaw('LOWER(last_name) = ?', [strtolower(trim($validated['last_name']))])
                  ->where('birth_date', $validated['birth_date']);
        }
        $existingReg = $query->first();

        // Gast-Upgrade nach Name/Birthdate suchen, falls Mitglied kein Treffer
        if (!$existingReg && $validated['member_type'] === 'member') {
            $existingReg = Registration::whereRaw('LOWER(last_name) = ?', [strtolower(trim($validated['last_name']))])
                ->where('birth_date', $validated['birth_date'])
                ->where('member_type', 'guest')
                ->first();
        }

        // ── Zugangsstatus bestimmen ────────────────────────────────────
        $accessStatus  = 'red';
        $accessReason  = null;
        $paymentStatus = 'paid';

        if ($validated['member_type'] === 'guest') {
            $validated['member_number'] = null;
            $existingVisits = $existingReg?->trial_visits_count ?? 0;

            if ($existingVisits >= 3) {
                // Sollte durch die Sperre weiter unten eigentlich nicht erreicht werden,
                // aber als Absicherung:
                $accessStatus = 'red';
                $accessReason = 'Schnupperlimit ausgeschöpft (3/3)';
            } elseif ($existingVisits >= 1) {
                // Bereits mindestens 1 Besuch → kommt ins Modal beim nächsten Check-in
                $accessStatus = 'blue';
                $accessReason = 'Schnupperklettern: Besuch ' . ($existingVisits + 1) . ' von 3';
            } else {
                // Erstregistrierung
                $accessStatus = 'blue';
                $accessReason = null;
            }

        } elseif ($validated['member_type'] === 'member') {
            if (!$member) {
                $accessStatus  = 'orange';
                $accessReason  = 'Mitglied noch unbestätigt / nicht in Datenbank';
                $paymentStatus = 'overdue';
            } elseif (($member->membership_status ?? null) !== 'active') {
                $accessStatus  = 'red';
                $accessReason  = 'Mitgliedschaft inaktiv';
                $paymentStatus = 'overdue';
            } elseif (($member->payment_status ?? null) === 'overdue') {
                $accessStatus  = 'orange';
                $accessReason  = 'Beitrag offen';
                $paymentStatus = 'overdue';
            } else {
                $accessStatus = 'green';
                $accessReason = null;
            }
        }

        if ($needsSupervision) {
            $supervisionNote = 'Unter 14 – Aufsicht erforderlich';
            $accessReason = $accessReason
                ? $accessReason . ' · ' . $supervisionNote
                : $supervisionNote;
        
            // Status nur anpassen wenn nicht bereits schlechter (red bleibt red)
            if ($validated['member_type'] === 'member' && $accessStatus !== 'red') {
                if (!$request->boolean('supervision_confirmed')) {
                    $accessStatus = 'orange';
                }
                // supervision_confirmed = true → accessStatus bleibt was er war (green/orange)
            }
        }

        if ($needsParentConsent) {
            $consentNote = 'Jugendlicher (14–17)';
            $accessReason = $accessReason
                ? $accessReason . ' · ' . $consentNote
                : $consentNote;
            // accessStatus bleibt unverändert – identisch zur Erwachsenen-Logik
        }

        // ── GAST-BLOCK / Upgrade-Logik ────────────────────────────────
        if ($existingReg) {
            $isUpgrade = $existingReg->member_type === 'guest' && $validated['member_type'] === 'member';

            if (!$isUpgrade) {
                if ($existingReg->member_type === 'guest') {
                    if (($existingReg->trial_visits_count ?? 0) >= 3) {
                        throw ValidationException::withMessages([
                            'first_name' => 'Du hast das Schnupper-Limit bereits vollständig ausgeschöpft. Eine weitere Registrierung als Gast ist nicht möglich.',
                        ]);
                    }
                    // Bereits registriert, aber noch Besuche übrig → nur erlaubt mit Staff-Freigabe
                    if (($existingReg->trial_visits_count ?? 0) >= 1) {
                        throw ValidationException::withMessages([
                            'first_name' => 'Du bist bereits als Schnuppergast registriert. Ein zweites Mal ist nur nach Absprache mit dem Hallendienst möglich.',
                        ]);
                    }
                } else {
                    return redirect('verify/' . $existingReg->qr_token)
                        ->with('success', 'Du warst bereits registriert! Hier ist dein aktueller Status.');
                }
            }

            $existingReg->update([
                'member_type'                => $validated['member_type'],
                'member_number'              => $validated['member_number'] ?? null,
                'waiver_accepted'            => true,
                'birth_date'                 => $validated['birth_date'],
                'email'                      => $validated['email'] ?? null,
                'access_status'              => $accessStatus,
                'access_reason'              => $accessReason,
                'payment_status'             => $paymentStatus,
                'needs_supervision'          => $needsSupervision,
                'needs_parent_consent'       => $needsParentConsent,
                'parent_consent_received'    => $needsParentConsent ? $existingReg->parent_consent_received : false,
                'parent_consent_received_at' => $needsParentConsent ? $existingReg->parent_consent_received_at : null,
                'supervision_confirmed'      => $needsSupervision
                    ? $request->boolean('supervision_confirmed')
                    : false,
            ]);
            $registration = $existingReg;
        } else {
            $registration = Registration::create([
                'first_name'                 => $validated['first_name'],
                'last_name'                  => $validated['last_name'],
                'birth_date'                 => $validated['birth_date'],
                'email'                      => $validated['email'] ?? null,
                'member_type'                => $validated['member_type'],
                'member_number'              => $validated['member_number'] ?? null,
                'waiver_accepted'            => true,
                'waiver_version'             => 'v1',
                'payment_status'             => $paymentStatus,
                'access_status'              => $accessStatus,
                'access_reason'              => $accessReason,
                'trial_visits_count'         => 0,
                'needs_supervision'          => $needsSupervision,
                'needs_parent_consent'       => $needsParentConsent,
                'parent_consent_received'    => false,
                'parent_consent_received_at' => null,
                'supervision_confirmed'      => $needsSupervision
                    ? $request->boolean('supervision_confirmed')
                    : false,
                'qr_token'                   => (string) Str::uuid(),
            ]);
        }

        return redirect('verify/' . $registration->qr_token)
            ->with('success', 'Registrierung erfolgreich!');
    }

    public function verify(string $token)
    {
        $registration = Registration::with('currentCheckin')
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('verify', compact('registration'));
    }

    public function checkin(Request $request, string $token)
    {
        $registration = Registration::with('currentCheckin')
            ->where('qr_token', $token)
            ->first();

        if (!$registration) {
            $msg = 'QR-Code ungültig oder abgelaufen.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : abort(404, $msg);
        }

        $hasActiveKulanz = $registration->manual_exception_until &&
                           $registration->manual_exception_until->isFuture();
        $needsKulanz = in_array($registration->access_status, ['red', 'orange']) && !$hasActiveKulanz;

        if ($needsKulanz) {
            $statusText = strtoupper($registration->access_status);
            $message    = "Check-in blockiert! Status ist {$statusText}. Kulanz erforderlich: "
                        . ($registration->access_reason ?? 'Unbekannt') . '.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->route('verify', $registration->qr_token)->withErrors($message);
        }

        if ($registration->currentCheckin) {
            $message = $registration->first_name . ' ' . $registration->last_name
                . ' ist bereits seit '
                . $registration->currentCheckin->checked_in_at->format('H:i')
                . ' Uhr eingecheckt.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('verify', $registration->qr_token)->withErrors($message);
        }

        // Nur green/blue dürfen direkt einchecken
        if (!in_array($registration->access_status, ['green', 'blue'])) {
            $message = $registration->access_status === 'red'
                ? 'Kein Zutritt erlaubt.'
                : 'Zutritt erfordert manuelle Freigabe durch den Hallendienst.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->withErrors('Check-in verweigert: ' . $message);
        }

        Checkin::create([
            'registration_id' => $registration->id,
            'checked_in_at'   => now(),
        ]);

        $registration->increment('trial_visits_count');
        $registration->refresh();

        // NEU: Schnuppergast nach Check-in auf orange setzen
        if ($registration->member_type === 'guest') {
            if ($registration->trial_visits_count >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Schnupperlimit ausgeschöpft (3/3)',
                ]);
            } else {
                $registration->update([
                    'access_status' => 'orange',
                    'access_reason' => 'Schnupperklettern – letzter Besuch am ' . now()->format('d.m.Y H:i') . ' Uhr',
                ]);
            }
        }

        $message = $registration->first_name . ' ' . $registration->last_name . ' wurde erfolgreich eingecheckt.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return redirect()->route('verify', $registration->qr_token)->with('success', $message);
    }
}
