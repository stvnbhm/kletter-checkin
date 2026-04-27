<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $registration_id
 * @property \Illuminate\Support\Carbon $checked_in_at
 * @property \Illuminate\Support\Carbon|null $checked_out_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Registration $registration
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereCheckedOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereRegistrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Checkin extends Model
{
    protected $fillable = [
        'registration_id',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}