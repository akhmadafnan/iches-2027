<?php

namespace App\Livewire\Reviewer;

use App\Actions\Reviewer\ClaimPaper;
use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\PaperReviewClaim;
use App\Models\User;
use App\Services\Reviewer\ReviewerPaperAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Reviewer Workspace | ICHES 2027')]
class AvailablePapers extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'available';

    public string $search = '';

    public string $trackFilter = '';

    public bool $claimModalOpen = false;

    public ?string $confirmingPublicId = null;

    public string $confirmingReference = '';

    public function mount(): void
    {
        Gate::authorize('review-papers');
        if (! in_array($this->tab, ['available', 'mine'], true)) {
            $this->tab = 'available';
        }
    }

    public function selectTab(string $tab): void
    {
        abort_unless(in_array($tab, ['available', 'mine'], true), 404);
        $this->tab = $tab;
        $this->resetPage();
        $this->cancelClaim();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTrackFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->trackFilter = '';
        $this->resetPage();
    }

    public function askClaim(string $publicId, ReviewerPaperAccess $access): void
    {
        Gate::authorize('review-papers');

        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $paper = $access->availableQuery($reviewer)
            ->where('public_id', $publicId)
            ->firstOrFail();

        $this->resetValidation('claim');
        $this->confirmingPublicId = $paper->public_id;
        $this->confirmingReference = $paper->reference ?? '';
        $this->claimModalOpen = true;
    }

    public function cancelClaim(): void
    {
        $this->claimModalOpen = false;
        $this->confirmingPublicId = null;
        $this->confirmingReference = '';
        $this->resetValidation('claim');
    }

    public function confirmClaim(ClaimPaper $action): void
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        if (! $this->claimModalOpen || $this->confirmingPublicId === null) {
            return;
        }

        $claim = $action->handle($reviewer, $this->confirmingPublicId);
        $this->cancelClaim();
        $this->tab = 'mine';
        $this->resetPage();
        session()->flash('success', "Paper {$claim->paper->reference} is now assigned to you for review.");
    }

    public function render(ReviewerPaperAccess $access): View
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $available = $access->availableQuery($reviewer);
        $totalAvailable = (clone $available)->count();
        $activeTracksInPool = (clone $available)->distinct()->count('conference_track_id');
        $totalAssigned = PaperReviewClaim::query()
            ->where('reviewer_id', $reviewer->id)
            ->whereNotNull('active_paper_id')
            ->count();

        // No author/affiliation relations are loaded or exposed in the UI.
        $query = $this->tab === 'mine'
            ? Paper::query()
                ->whereHas('reviewClaims', fn (Builder $q) => $q
                    ->where('reviewer_id', $reviewer->id)
                    ->whereNotNull('active_paper_id'))
            : $available;

        $papers = $query
            ->with('conferenceTrack:id,name')
            ->when($this->trackFilter !== '', fn (Builder $builder) => $builder
                ->where('conference_track_id', $this->trackFilter))
            ->when(trim($this->search) !== '', function (Builder $builder): void {
                $term = '%'.trim($this->search).'%';
                $builder->where(fn (Builder $filter) => $filter
                    ->where('title', 'like', $term)
                    ->orWhere('reference', 'like', $term));
            })
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.reviewer.available-papers', [
            'papers' => $papers,
            'tracks' => ConferenceTrack::query()
                ->whereHas('program', fn (Builder $builder) => $builder
                    ->where('code', ProgramCode::Conference->value))
                ->orderBy('sort_order')
                ->get(['id', 'name']),
            'totalAvailable' => $totalAvailable,
            'activeTracksInPool' => $activeTracksInPool,
            'totalAssigned' => $totalAssigned,
        ]);
    }
}
