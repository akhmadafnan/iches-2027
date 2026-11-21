<?php

namespace App\Services\Academic;

use App\Enums\PaperReviewStatus;
use App\Enums\PaperStatus;
use App\Models\Paper;
use Illuminate\Database\Eloquent\Builder;

final class ReadyAcademicDecisions
{
    /** @return Builder<Paper> */
    public function query(): Builder
    {
        return Paper::query()
            ->where('status', PaperStatus::Submitted->value)
            ->whereDoesntHave('decision')
            ->whereHas('reviews', function (Builder $review): void {
                $review->where('status', PaperReviewStatus::Submitted->value)
                    ->whereIn('recommendation', ['accept', 'reject'])
                    ->whereHas('claim', fn (Builder $claim) => $claim->whereColumn('active_paper_id', 'papers.id'));
            });
    }
}
