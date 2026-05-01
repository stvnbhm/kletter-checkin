<?php

namespace App\Console\Commands;

use App\Models\Checkin;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCheckoutExpiredCheckins extends Command
{
    protected $signature = 'checkins:auto-checkout';

    protected $description = 'Schließt offene Check-ins automatisch nach 3 Stunden';

    public function handle(): int
    {
        $expiredCheckins = Checkin::whereNull('checked_out_at')
            ->where('checked_in_at', '<=', now()->subHours(3))
            ->get();

        $closedCount = 0;

        foreach ($expiredCheckins as $checkin) {
            $checkedOutAt = Carbon::parse($checkin->checked_in_at)->copy()->addHours(3);
            $checkin->update(['checked_out_at' => $checkedOutAt]);
        
            $registration = Registration::find($checkin->registration_id);
            if ($registration) {
                $hasOpenCheckin = Checkin::where('registration_id', $registration->id)
                    ->whereNull('checked_out_at')
                    ->exists();
        
                if (!$hasOpenCheckin) {
                    $registration->update(['checked_in_at' => null]);
                }
        
                // Schnuppergast: nach 3 Besuchen → red
                if ($registration->member_type === 'guest'
                    && $registration->trial_visits_count >= 3) {
                    $registration->update([
                        'access_status' => 'red',
                        'access_reason' => 'Schnupperlimit ausgeschöpft (3/3)',
                    ]);
                }
        
                // NEU: Unverified Member: nach 3 Besuchen → red
                $isUnverifiedMember = $registration->member_type === 'member'
                                      && $registration->member === null;
        
                if ($isUnverifiedMember && $registration->trial_visits_count >= 3) {
                    $registration->update([
                        'access_status' => 'red',
                        'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                    ]);
                }
            }
        
            $closedCount++;
        }

        $this->info("Auto-Checkout abgeschlossen: {$closedCount} Check-ins geschlossen.");

        return self::SUCCESS;
    }
}