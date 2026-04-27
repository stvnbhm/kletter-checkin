<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $member_number
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string $membership_status
 * @property string $payment_status
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property \Illuminate\Support\Carbon|null $last_imported_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereLastImportedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereMemberNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereMembershipStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Member extends Model
{
    protected $fillable = [
        'member_number',
        'first_name',
        'last_name',
        'email',
        'membership_status',
        'payment_status',
        'birth_date',
        'last_imported_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'last_imported_at' => 'datetime',
        ];
    }
}