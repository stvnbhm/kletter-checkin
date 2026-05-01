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
        if (filled($request->input('website')) || filled($request->input('faxnumber'))) {
            \Log::warning('Spam blockiert: Honeypot-Feld ausgefüllt', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            throw ValidationException::withMessages([
                'firstname' => 'Die Registrierung konnte nicht verarbeitet werden. Bitte versuche es erneut.',
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
                'firstname' => 'Die Registrierung konnte nicht verarbeitet werden. Bitte versuche es erneut.',
            ]);
        }

        $validated = $request->validate([
            'firstname'          => 'required|string|max:255',
            'lastname'           => 'required|string|max:255',
            'birthdate'          => 'required|date|after_or_equal:1900-01-01|before_or_equal:today',
            'email'              => 'nullable|email|max:255',
            'membertype'         => 'required|in:member,guest',
            'membernumber'       => [
                'required_if:membertype,member',
                'nullable',
                'string',
                // ✅ NEU: Format XX-XXXXX erzwingen
                'regex:/^\d{2}-\d{5}$/',
            ],
            'waiveraccepted'     => 'required|accepted',
            'rules_accepted'     => 'required|accepted',
            'supervisionconfirmed' => 'nullable|boolean',
            // Honeypot-Felder
            'hptime'             => 'required|integer',
            'website'            => 'nullable|max:0',
            'faxnumber'          => 'nullable|max:0',
        ], [
            'birthdate.required'   => 'Das Geburtsdatum ist erforderlich, um doppelte Registrierungen zu vermeiden.',
            // ✅ NEU: Fehlermeldung für Format-Validierung
            'membernumber.regex'   => 'Die Mitgliedsnummer muss im Format XX-XXXXX eingegeben werden (z.B. 12-34567).',
        ]);

        // Freitextfelder bereinigen
        $validated['firstname']    = strip_tags($validated['firstname']);
        $validated['lastname']     = strip_tags($validated['lastname']);
        $validated['membernumber'] = isset($validated['membernumber'])
            ? strip_tags($validated['membernumber'])
            : null;

        $birthDate       = Carbon::parse($validated['birthdate']);
        $age             = $birthDate->age;
        $needsSupervision   = $age < 14;
        $needsParentConsent = $age >= 14 && $age < 18;

        if ($needsSupervision && !$request->boolean('supervisionconfirmed')) {
            throw ValidationException::withMessages([
                'supervisionconfirmed' => 'Für Kinder unter 14 Jahren muss bestätigt werden, dass Klettern nur unter Aufsicht erfolgt.',
            ]);
        }

        // Mitglieds-Verifikation gegen Mitgliederliste
        $member = null;
        if ($validated['membertype'] === 'member') {
            $member = DB::table('members')
                ->where('member_number', $validated['membernumber'])
                ->first();

            if ($member) {
                $lastNameInput = strtolower(trim($validated['lastname']));
                $lastNameCsv   = strtolower(trim($member->lastname ?? ''));

                // ✅ NEU: Geburtsdatum zusätzlich abgleichen
                $birthInput = Carbon::parse($validated['birthdate'])->toDateString();
                $birthCsv   = $member->birthdate
                    ? Carbon::parse($member->birthdate)->toDateString()
                    : null;

                if ($lastNameInput !== $lastNameCsv || $birthInput !== $birthCsv) {
                    throw ValidationException::withMessages([
                        'membernumber' => 'Die Mitgliedsnummer stimmt nicht mit den angegebenen Daten (Nachname + Geburtsdatum) überein. Bitte prüfen!',
                    ]);
                }
            }
        }

        // Duplikat-Suche
        $query = Registration::query();
        if ($validated['membertype'] === 'member' && !empty($validated['membernumber'])) {
            $query->where('member_number', $validated['membernumber']);
        } else {
            $query->whereRaw('LOWER(lastname) = ?', [strtolower(trim($validated['lastname']))])
                  ->where('birthdate', $validated['birthdate']);
        }
        $existingReg = $query->first();

        // FIX 1: Gast-Upgrade nach Name/Birthdate suchen, falls Mitglied kein Treffer
        if (!$existingReg && $validated['membertype'] === 'member') {
            $existingReg = Registration::whereRaw('LOWER(lastname) = ?', [strtolower(trim($validated['lastname']))])
                ->where('birthdate', $validated['birthdate'])
                ->where('membertype', 'guest')
                ->first();
        }

        // Zugangsstatus bestimmen
        $accessStatus = 'red';
        $accessReason = 'Unbekannt';
        $paymentStatus = 'paid';

        if ($validated['membertype'] === 'guest') {
            $validated['membernumber'] = null;
            if (!$existingReg) {
                $accessStatus = 'blue';
                $accessReason = 'Schnupperklettern';
            } else {
                $accessStatus = 'orange';
                $accessReason = 'Kulanz: ' . $existingReg->manualexceptionreason;
            }
        } elseif ($validated['membertype'] === 'member') {
            if (!$member) {
                $accessStatus  = 'orange';
                $accessReason  = 'Mitglied noch unbestätigt / nicht in Datenbank';
                $paymentStatus = 'overdue';
            } elseif (($member->membershipstatus ?? null) !== 'active') {
                $accessStatus  = 'red';
                $accessReason  = 'Mitgliedschaft inaktiv';
                $paymentStatus = 'overdue';
            } elseif (($member->paymentstatus ?? null) === 'open') {
                $accessStatus  = 'orange';
                $accessReason  = 'Beitrag offen';
                $paymentStatus = 'overdue';
            } else {
                $accessStatus = 'green';
                $accessReason = 'Mitgliedschaft aktiv & bezahlt';
            }
        }

        // Aufsicht-Logik
        if ($needsSupervision) {
            if ($validated['membertype'] === 'guest') {
                $accessReason .= ' · Unter 14 – Aufsicht erforderlich';
            } else {
                if ($request->boolean('supervisionconfirmed')) {
                    $accessStatus = 'green';
                    $accessReason = 'Unter 14 – Aufsicht erforderlich';
                } else {
                    $accessStatus = 'orange';
                    $accessReason = 'Unter 14 – Aufsicht erforderlich';
                }
            }
        }

        if ($needsParentConsent) {
            if ($validated['membertype'] === 'guest') {
                $accessReason .= ' · Jugendlicher (14–17)';
            } else {
                $accessStatus = 'green';
                $accessReason = 'Jugendlicher (14–17)';
            }
        }

        // GAST-BLOCK / Upgrade-Logik
        if ($existingReg) {
            // FIX 2: Upgrade-Logik Gast → Mitglied erlauben
            $isUpgrade = $existingReg->membertype === 'guest' && $validated['membertype'] === 'member';

            if (!$isUpgrade) {
                if ($existingReg->membertype === 'guest') {
                    if (($existingReg->trialvisitscount ?? 0) >= 2) {
                        throw ValidationException::withMessages([
                            'firstname' => 'Du hast das Schnupper-Limit bereits vollständig ausgeschöpft. Eine weitere Registrierung als Gast ist nicht möglich.',
                        ]);
                    }
                    $hasKulanz = $existingReg->manualexceptionuntil &&
                                 $existingReg->manualexceptionuntil->isFuture();
                    if (!$hasKulanz) {
                        throw ValidationException::withMessages([
                            'firstname' => 'Du bist bereits als Schnuppergast registriert. Ein zweites Mal ist nur nach Absprache mit dem Hallendienst möglich.',
                        ]);
                    }
                } else {
                    return redirect('verify/' . $existingReg->qrtoken)
                        ->with('success', 'Du warst bereits registriert! Hier ist dein aktueller Status.');
                }
            }

            // FIX 3: membertype & membernumber mitschreiben
            $existingReg->update([
                'membertype'              => $validated['membertype'],
                'membernumber'            => $validated['membernumber'] ?? null,
                'waiveraccepted'          => true,
                'birthdate'               => $validated['birthdate'],
                'email'                   => $validated['email'] ?? null,
                'accessstatus'            => $accessStatus,
                'accessreason'            => $accessReason,
                'needssupervision'        => $needsSupervision,
                'needsparentconsent'      => $needsParentConsent,
                'parentconsentreceived'   => $needsParentConsent ? $existingReg->parentconsentreceived : false,
                'parentconsentreceivedat' => $needsParentConsent ? $existingReg->parentconsentreceivedat : null,
                'supervisionconfirmed'    => $needsSupervision
                    ? $request->boolean('supervisionconfirmed')
                    : false,
            ]);
            $registration = $existingReg;
        } else {
            $registration = Registration::create([
                'firstname'               => $validated['firstname'],
                'lastname'                => $validated['lastname'],
                'birthdate'               => $validated['birthdate'],
                'email'                   => $validated['email'] ?? null,
                'membertype'              => $validated['membertype'],
                'membernumber'            => $validated['membernumber'] ?? null,
                'waiveraccepted'          => true,
                'waiverversion'           => 'v1',
                'paymentstatus'           => $paymentStatus,
                'accessstatus'            => $accessStatus,
                'accessreason'            => $accessReason,
                'trialvisitscount'        => 0,
                'needssupervision'        => $needsSupervision,
                'needsparentconsent'      => $needsParentConsent,
                'parentconsentreceived'   => false,
                'parentconsentreceivedat' => null,
                'supervisionconfirmed'    => $needsSupervision
                    ? $request->boolean('supervisionconfirmed')
                    : false,
                'qrtoken'                 => (string) Str::uuid(),
            ]);
        }

        return redirect('verify/' . $registration->qrtoken)
            ->with('success', 'Registrierung erfolgreich!');
    }

    public function verify(string $token)
    {
        $registration = Registration::withCurrentCheckin()
            ->where('qrtoken', $token)
            ->firstOrFail();

        return view('verify', compact('registration'));
    }

    public function checkin(Request $request, string $token)
    {
        $registration = Registration::withCurrentCheckin()
            ->where('qrtoken', $token)
            ->first();

        if (!$registration) {
            $msg = 'QR-Code ungültig oder abgelaufen.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : abort(404, $msg);
        }

        $hasActiveKulanz = $registration->manualexceptionuntil &&
                           $registration->manualexceptionuntil->isFuture();
        $needsKulanz = in_array($registration->accessstatus, ['red', 'orange']) && !$hasActiveKulanz;

        if ($needsKulanz) {
            $statusText = strtoupper($registration->accessstatus);
            $message    = "Check-in blockiert! Status ist {$statusText}. Kulanz erforderlich: "
                        . ($registration->accessreason ?? 'Unbekannt') . '.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->route('verify', $registration->qrtoken)->withErrors($message);
        }

        if ($registration->currentCheckin) {
            $message = $registration->firstname . ' ' . $registration->lastname
                . ' ist bereits seit '
                . $registration->currentCheckin->checkedinat->format('H:i')
                . ' Uhr eingecheckt.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('verify', $registration->qrtoken)->withErrors($message);
        }

        // Nur green/blue dürfen direkt einchecken
        if (!in_array($registration->accessstatus, ['green', 'blue'])) {
            $message = $registration->accessstatus === 'red'
                ? 'Kein Zutritt erlaubt.'
                : 'Zutritt erfordert manuelle Freigabe durch den Hallendienst.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->withErrors('Check-in verweigert: ' . $message);
        }

        Checkin::create([
            'registration_id' => $registration->id,
            'checkedinat'     => now(),
        ]);
        $registration->increment('trialvisitscount');

        $message = $registration->firstname . ' ' . $registration->lastname
                 . ' wurde erfolgreich eingecheckt.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return redirect()->route('verify', $registration->qrtoken)->with('success', $message);
    }
}