<?php

namespace App\Livewire\Reviewer;

use App\Actions\Reviewer\ClaimPaper;
use App\Actions\Reviewer\SavePaperReview;
use App\Enums\PaperReviewStatus;
use App\Models\Paper;
use App\Models\PaperReview;
use App\Models\User;
use App\Services\Reviewer\ReviewerPaperAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Review Paper Preview | ICHES 2027')]
class PaperPreview extends Component
{
    public string $paperPublicId;

    public bool $claimModalOpen = false;

    public bool $submitReviewModalOpen = false;

    public string $generalComments = '';

    public ?string $recommendation = null;

    public int $reviewVersion = 0;

    public function mount(Paper $paper, ReviewerPaperAccess $access): void
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);
        abort_unless($access->canPreview($paper, $reviewer), 404);

        $this->paperPublicId = $paper->public_id;
        $this->loadCurrentReview($paper, $reviewer);
    }

    private function loadCurrentReview(Paper $paper, User $reviewer): void
    {
        $review = PaperReview::query()
            ->where('paper_id', $paper->id)
            ->where('reviewer_id', $reviewer->id)
            ->whereHas('claim', fn ($query) => $query
                ->where('active_paper_id', $paper->id))
            ->first();

        $this->generalComments = $review === null ? '' : ($review->general_comments ?? '');
        $this->recommendation = $review?->recommendation?->value;
        $this->reviewVersion = $review === null ? 0 : $review->lock_version;
    }

    public function askClaim(ReviewerPaperAccess $access): void
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $paper = Paper::query()->where('public_id', $this->paperPublicId)->firstOrFail();

        if (! $access->isClaimable($paper, $reviewer)) {
            $this->addError('claim', 'This abstract is no longer available to claim.');

            return;
        }

        $this->resetValidation('claim');
        $this->claimModalOpen = true;
    }

    public function cancelClaim(): void
    {
        $this->claimModalOpen = false;
        $this->resetValidation('claim');
    }

    public function confirmClaim(ClaimPaper $action): void
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        if (! $this->claimModalOpen) {
            return;
        }

        $claim = $action->handle($reviewer, $this->paperPublicId);
        session()->flash('success', "Paper {$claim->paper->reference} is now assigned to you for review.");
        $this->redirect(route('reviewer.workspace', ['tab' => 'mine']), navigate: true);
    }

    public function saveReviewDraft(SavePaperReview $action): void
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $review = $action->handle(
            $reviewer,
            $this->paperPublicId,
            $this->generalComments,
            $this->recommendation === '' ? null : $this->recommendation,
            $this->reviewVersion,
        );

        $this->reviewVersion = $review->lock_version;
        $this->resetValidation();
        session()->flash('review_saved', 'Your review draft was saved. You can continue editing before submission.');
    }

    public function askSubmitReview(): void
    {
        Gate::authorize('review-papers');

        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $paper = Paper::query()->where('public_id', $this->paperPublicId)->firstOrFail();
        $claim = $paper->reviewClaims()
            ->where('reviewer_id', $reviewer->id)
            ->where('active_paper_id', $paper->id)
            ->firstOrFail();

        abort_if($claim->review?->status === PaperReviewStatus::Submitted, 403);

        Validator::make([
            'generalComments' => $this->generalComments,
            'recommendation' => $this->recommendation,
        ], [
            'generalComments' => ['required', 'string', 'max:10000'],
            'recommendation' => ['required', 'in:accept,reject'],
        ])->validate();

        $this->resetValidation();
        $this->submitReviewModalOpen = true;
    }

    public function cancelSubmitReview(): void
    {
        $this->submitReviewModalOpen = false;
        $this->resetValidation();
    }

    public function confirmSubmitReview(SavePaperReview $action): void
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        if (! $this->submitReviewModalOpen) {
            return;
        }

        $review = $action->handle(
            $reviewer,
            $this->paperPublicId,
            $this->generalComments,
            $this->recommendation === '' ? null : $this->recommendation,
            $this->reviewVersion,
            submit: true,
        );

        $this->reviewVersion = $review->lock_version;
        $this->submitReviewModalOpen = false;
        $this->resetValidation();
        session()->flash('review_submitted', 'Review submitted successfully. Your recommendation is now internal and read-only.');
    }

    public function render(ReviewerPaperAccess $access): View
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $paper = Paper::query()
            ->where('public_id', $this->paperPublicId)
            ->with('conferenceTrack')
            ->firstOrFail();

        abort_unless($access->canPreview($paper, $reviewer), 404);

        $activeClaim = $paper->reviewClaims()
            ->where('reviewer_id', $reviewer->id)
            ->where('active_paper_id', $paper->id)
            ->with('review')
            ->first();

        return view('livewire.reviewer.paper-preview', [
            'paper' => $paper,
            'canClaim' => $access->isClaimable($paper, $reviewer),
            'claimedByMe' => $activeClaim !== null,
            'claimedAt' => $activeClaim?->claimed_at,
            'review' => $activeClaim?->review,
            'reviewSubmitted' => $activeClaim?->review?->status === PaperReviewStatus::Submitted,
        ]);
    }
}
