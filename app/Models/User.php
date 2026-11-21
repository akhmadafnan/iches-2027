<?php

namespace App\Models;

use App\Enums\RegistrationIntention;
use App\Enums\StaffDuty;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $institution
 * @property string|null $country
 * @property string|null $profile_photo_path
 * @property bool $profile_setup_required
 * @property RegistrationIntention|null $registration_intention
 * @property UserRole $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'institution', 'country', 'registration_intention', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'registration_intention' => RegistrationIntention::class,
            'role' => UserRole::class,
            'profile_setup_required' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** @return HasMany<StaffAssignment, $this> */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    /** @return HasMany<Paper, $this> */
    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class, 'owner_id');
    }

    public function hasGlobalDuty(StaffDuty $duty): bool
    {
        if ($duty->requiresScope()) {
            return false;
        }

        return $this->staffAssignments()
            ->where('duty', $duty->value)
            ->where('scope_type', 'global')
            ->where('scope_id', 0)
            ->exists();
    }

    public function hasScopedDuty(StaffDuty $duty, string $scopeType, int $scopeId): bool
    {
        if (! $duty->requiresScope() || $scopeId < 1) {
            return false;
        }

        return $this->staffAssignments()
            ->where('duty', $duty->value)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->exists();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
