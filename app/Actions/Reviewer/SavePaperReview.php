<?php

namespace App\Actions\Reviewer;

use App\Enums\PaperReviewRecommendation;
use App\Enums\PaperReviewStatus;
use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\PaperReview;
use App\Models\PaperReviewClaim;
use App\Models\User;
use App\Services\Reviewer\ReviewerPaperAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class SavePaperReview
{
    public function __construct(private readonly ReviewerPaperAccess $access) {}

    public function handle(
        User $reviewer,
        string $paperPublicId,
        string $comments,
        ?string $recommendation,
        int $expectedVersion,
        bool $submit = false,
    ): PaperReview {
        Gate::forUser($reviewer)->authorize('review-papers');

        return DB::transaction(function () use ($reviewer, $paperPublicId, $comments, $recommendation, $expectedVersion, $submit): PaperReview {
            $paper = Paper::query()->where('public_id', $paperPublicId)->lockForUpdate()->firstOrFail();

            if ($paper->status !== PaperStatus::Submitted || $this->access->authoredBy($paper, $reviewer)) {
                abort(404);
            }

            $claim = PaperReviewClaim::query()
                ->where('paper_id', $paper->id)
                ->where('reviewer_id', $reviewer->id)
                ->where('active_paper_id', $paper->id)
                ->lockForUpdate()
                ->firstOrFail();

            $review = PaperReview::query()
                ->where('paper_review_claim_id', $claim->id)
                ->lockForUpdate()
                ->first();

            if ($review !== null && $review->status === PaperReviewStatus::Submitted) {
                throw ValidationException::withMessages(['review' => 'This review has already been submitted and is read-only.']);
            }

            if ($expectedVersion < 0 || $expectedVersion !== ($review === null ? 0 : $review->lock_version)) {
                throw ValidationException::withMessages(['review' => 'This review changed in another tab. Refresh to load the latest saved version before editing.']);
            }

            $cleanComments = trim($comments);
            if (mb_strlen($cleanComments) > 10000) {
                throw ValidationException::withMessages(['generalComments' => 'General Comments cannot exceed 10,000 characters.']);
            }

            if ($recommendation !== null && ! in_array($recommendation, ['accept', 'reject'], true)) {
                throw ValidationException::withMessages(['recommendation' => 'Choose Accept or Reject only.']);
            }

            if ($submit && ($cleanComments === '' || $recommendation === null)) {
                $issues = [];
                if ($cleanComments === '') {
                    $issues['generalComments'] = 'General Comments are required to submit the review.';
                }
                if ($recommendation === null) {
                    $issues['recommendation'] = 'Choose Accept or Reject before submitting.';
                }

                throw ValidationException::withMessages($issues);
            }

            $now = Carbon::now();
            if ($review === null) {
                $review = new PaperReview;
                $review->paper_review_claim_id = $claim->id;
                $review->paper_id = $paper->id;
                $review->reviewer_id = $reviewer->id;
                $review->lock_version = 0;
            }

            $review->general_comments = $cleanComments === '' ? null : $cleanComments;
            $review->recommendation = $recommendation === null
                ? null
                : PaperReviewRecommendation::from($recommendation);
            $review->status = $submit ? PaperReviewStatus::Submitted : PaperReviewStatus::Draft;
            $review->submitted_at = $submit ? $now : null;
            $review->lock_version++;
            $review->save();

            $review->events()->create([
                'actor_id' => $reviewer->id,
                'event' => $submit ? 'review_submitted' : 'draft_saved',
                'version' => $review->lock_version,
                'occurred_at' => $now,
            ]);

            return $review;
        }, 3);
    }
}
