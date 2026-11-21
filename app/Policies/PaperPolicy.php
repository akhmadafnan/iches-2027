<?php

namespace App\Policies;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\User;

class PaperPolicy
{
    public function viewAny(User $actor): bool
    {
        return true;
    }

    public function create(User $actor): bool
    {
        return true;
    }

    public function view(User $actor, Paper $paper): bool
    {
        return $paper->owner_id === $actor->id;
    }

    public function submit(User $actor, Paper $paper): bool
    {
        return $paper->owner_id === $actor->id
            && $paper->status === PaperStatus::Draft;
    }

    public function update(User $actor, Paper $paper): bool
    {
        return $paper->owner_id === $actor->id
            && $paper->status === PaperStatus::Draft;
    }
}
