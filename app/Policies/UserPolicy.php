<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->role === UserRole::Admin;
    }

    public function updateAccess(User $actor, User $target): bool
    {
        return $actor->role === UserRole::Admin
            && $actor->isNot($target);
    }
}
