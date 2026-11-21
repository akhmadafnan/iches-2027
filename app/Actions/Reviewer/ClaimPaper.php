<?php

namespace App\Actions\Reviewer;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\PaperReviewClaim;
use App\Models\User;
use App\Services\Reviewer\ReviewerPaperAccess;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ClaimPaper
{
    public function __construct(private readonly ReviewerPaperAccess $access) {}

    public function handle(User $reviewer, string $paperPublicId): PaperReviewClaim
    {
        Gate::forUser($reviewer)->authorize('review-papers');

        try {
            return DB::transaction(function () use ($reviewer, $paperPublicId): PaperReviewClaim {
                $paper = Paper::query()
                    ->where('public_id', $paperPublicId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($paper->status !== PaperStatus::Submitted) {
                    throw ValidationException::withMessages([
                        'claim' => 'Only submitted abstracts can be claimed.',
                    ]);
                }

                if ($this->access->authoredBy($paper, $reviewer)) {
                    throw ValidationException::withMessages([
                        'claim' => 'You cannot claim a paper you authored.',
                    ]);
                }

                if ($paper->reviewClaims()->whereNotNull('active_paper_id')->exists()) {
                    throw ValidationException::withMessages([
                        'claim' => 'This abstract has already been claimed by another reviewer.',
                    ]);
                }

                return PaperReviewClaim::query()->create([
                    'paper_id' => $paper->id,
                    'active_paper_id' => $paper->id,
                    'reviewer_id' => $reviewer->id,
                    'claimed_at' => now(),
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException) {
            // Defensive second line of protection for simultaneous requests
            // on engines that do not implement SELECT ... FOR UPDATE.
            throw ValidationException::withMessages([
                'claim' => 'This abstract has already been claimed by another reviewer.',
            ]);
        }
    }
}
