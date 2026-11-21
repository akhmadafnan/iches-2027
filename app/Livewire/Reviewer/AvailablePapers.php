<?php

namespace App\Livewire\Reviewer;

use App\Enums\PaperStatus;
use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Reviewer Workspace | ICHES 2027')]
class AvailablePapers extends Component
{
    use WithPagination;

    public string $search = '';

    public string $trackFilter = '';

    public function mount(): void
    {
        Gate::authorize('review-papers');
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

    public function render(): View
    {
        Gate::authorize('review-papers');
        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        // This is a read-only index. REV-002 will introduce claim eligibility
        // and concurrency rules; the current pool cannot be claimed yet.
        $query = Paper::query()
            ->where('status', PaperStatus::Submitted->value)
            ->where('owner_id', '!=', $reviewer->id);

        $totalAvailable = (clone $query)->count();
        $activeTracksInPool = (clone $query)->distinct()->count('conference_track_id');

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
        ]);
    }
}
