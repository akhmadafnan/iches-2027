<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserRoleAssignment extends Model
{
    protected $table = 'user_roles';

    protected $fillable = ['role'];

    protected function casts(): array
    {
        return ['role' => UserRole::class];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
