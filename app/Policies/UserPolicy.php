<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->activeRole(), [UserRole::Admin, UserRole::Superadmin], true);
    }

    public function updateAccess(User $actor, User $target): bool
    {
        return $actor->isNot($target)
            && in_array($actor->activeRole(), [UserRole::Admin, UserRole::Superadmin], true)
            && ($actor->isActiveAs(UserRole::Superadmin) || ! $target->hasRole(UserRole::Superadmin));
    }
}
