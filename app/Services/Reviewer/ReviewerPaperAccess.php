<?php

namespace App\Services\Reviewer;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ReviewerPaperAccess
{
    /**
     * Papers eligible for a new claim by this reviewer. The application still
     * rechecks each constraint in the locked transactional ClaimPaper action.
     *
     * @return Builder<Paper>
     */
    public function availableQuery(User $reviewer): Builder
    {
        return Paper::query()
            ->where('status', PaperStatus::Submitted->value)
            ->where('owner_id', '!=', $reviewer->id)
            ->whereDoesntHave('authors', fn (Builder $q) => $q
                ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($reviewer->email))]))
            ->whereDoesntHave('reviewClaims', fn (Builder $q) => $q
                ->whereNotNull('active_paper_id'));
    }

    public function authoredBy(Paper $paper, User $reviewer): bool
    {
        return $paper->owner_id === $reviewer->id
            || $paper->authors()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($reviewer->email))])
                ->exists();
    }

    public function canPreview(Paper $paper, User $reviewer): bool
    {
        if ($paper->status !== PaperStatus::Submitted || $this->authoredBy($paper, $reviewer)) {
            return false;
        }

        return ! $paper->reviewClaims()
            ->whereNotNull('active_paper_id')
            ->where('reviewer_id', '!=', $reviewer->id)
            ->exists();
    }

    public function isClaimable(Paper $paper, User $reviewer): bool
    {
        return $this->canPreview($paper, $reviewer)
            && ! $paper->reviewClaims()->whereNotNull('active_paper_id')->exists();
    }
}
