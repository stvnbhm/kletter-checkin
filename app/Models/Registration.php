<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property string|null $email
 * @property string $member_type
 * @property string|null $member_number
 * @property bool $waiver_accepted
 * @property string $waiver_version
 * @property string $payment_status
 * @property string $access_status
 * @property string|null $access_reason
 * @property string|null $manual_exception_reason
 * @property \Illuminate\Support\Carbon|null $manual_exception_until
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property string $qr_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $trial_visits_count
 * @property bool $needs_supervision
 * @property bool $needs_parent_consent
 * @property bool $parent_consent_received
 * @property \Illuminate\Support\Carbon|null $parent_consent_received_at
 * @property bool $supervision_confirmed
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Checkin> $checkins
 * @property-read int|null $checkins_count
 * @property-read \App\Models\Checkin|null $currentCheckin
 * @property-read string $full_name
 * @property-read bool $is_checked_in
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \App\Models\Member|null $member
 * @method static Builder<static>|Registration newModelQuery()
 * @method static Builder<static>|Registration newQuery()
 * @method static Builder<static>|Registration query()
 * @method static Builder<static>|Registration whereAccessReason($value)
 * @method static Builder<static>|Registration whereAccessStatus($value)
 * @method static Builder<static>|Registration whereBirthDate($value)
 * @method static Builder<static>|Registration whereCheckedInAt($value)
 * @method static Builder<static>|Registration whereCreatedAt($value)
 * @method static Builder<static>|Registration whereEmail($value)
 * @method static Builder<static>|Registration whereFirstName($value)
 * @method static Builder<static>|Registration whereId($value)
 * @method static Builder<static>|Registration whereLastName($value)
 * @method static Builder<static>|Registration whereManualExceptionReason($value)
 * @method static Builder<static>|Registration whereManualExceptionUntil($value)
 * @method static Builder<static>|Registration whereNotes($value)
 * @method static Builder<static>|Registration whereMemberNumber($value)
 * @method static Builder<static>|Registration whereMemberType($value)
 * @method static Builder<static>|Registration whereNeedsParentConsent($value)
 * @method static Builder<static>|Registration whereNeedsSupervision($value)
 * @method static Builder<static>|Registration whereParentConsentReceived($value)
 * @method static Builder<static>|Registration whereParentConsentReceivedAt($value)
 * @method static Builder<static>|Registration wherePaymentStatus($value)
 * @method static Builder<static>|Registration whereQrToken($value)
 * @method static Builder<static>|Registration whereSupervisionConfirmed($value)
 * @method static Builder<static>|Registration whereTrialVisitsCount($value)
 * @method static Builder<static>|Registration whereUpdatedAt($value)
 * @method static Builder<static>|Registration whereWaiverAccepted($value)
 * @method static Builder<static>|Registration whereWaiverVersion($value)
 * @mixin \Eloquent
 */
class Registration extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'email',
        'member_type',
        'member_number',
        'waiver_accepted',
        'waiver_version',
        'payment_status',
        'access_status',
        'qr_token',
        'checked_in_at',
        'manual_exception_reason',
        'manual_exception_until',
        'access_reason',
        'trial_visits_count',
        'needs_supervision',
        'needs_parent_consent',
        'parent_consent_received',
        'parent_consent_received_at',
        'supervision_confirmed',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'waiver_accepted' => 'boolean',
        'checked_in_at' => 'datetime',
        'manual_exception_until' => 'datetime',
        'needs_supervision' => 'boolean',
        'needs_parent_consent' => 'boolean',
        'parent_consent_received' => 'boolean',
        'parent_consent_received_at' => 'datetime',
        'supervision_confirmed' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_number', 'member_number');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getStatusColorAttribute(): string
    {
        return $this->access_status;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->access_status) {
            'green' => 'Mitglied aktiv',
            'blue' => 'Schnuppergast ok',
            'orange' => 'Warnung',
            default => 'Kein Zutritt',
        };
    }

    public function getIsCheckedInAttribute(): bool
    {
        return $this->currentCheckin !== null; // Greift auf die HasOne Relation zu
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Holt den aktuell offenen Check-in als echte HasOne Beziehung.
     * Dadurch können wir es im Controller mit ->with('currentCheckin') laden!
     */
    public function currentCheckin(): HasOne
    {
        return $this->hasOne(Checkin::class)->ofMany(
            ['checked_in_at' => 'max'],
            function (Builder $query) {
                $query->whereNull('checked_out_at');
            }
        );
    }
}