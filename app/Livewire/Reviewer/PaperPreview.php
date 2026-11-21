<?php

namespace App\Livewire\Reviewer;

use App\Actions\Reviewer\ClaimPaper;
use App\Models\Paper;
use App\Models\User;
use App\Services\Reviewer\ReviewerPaperAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Review Paper Preview | ICHES 2027')]
class PaperPreview extends Component
{
    public string $paperPublicId;

    public bool $claimModalOpen = false;

    public function mount(Paper $paper, ReviewerPaperAccess $access): void
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);
        abort_unless($access->canPreview($paper, $reviewer), 404);

        $this->paperPublicId = $paper->public_id;
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
            ->whereNotNull('active_paper_id')
            ->first(['id', 'claimed_at']);

        return view('livewire.reviewer.paper-preview', [
            'paper' => $paper,
            'canClaim' => $access->isClaimable($paper, $reviewer),
            'claimedByMe' => $activeClaim !== null,
            'claimedAt' => $activeClaim?->claimed_at,
        ]);
    }
}
