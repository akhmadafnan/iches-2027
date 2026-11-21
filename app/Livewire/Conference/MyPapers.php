<?php

namespace App\Livewire\Conference;

use App\Actions\Conference\SubmitPaper;
use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\User;
use App\Services\Conference\PaperSubmissionReadiness;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Submissions | ICHES 2027')]
class MyPapers extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $submitModalOpen = false;

    public ?string $confirmingPublicId = null;

    public string $confirmingReference = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Paper::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function askSubmit(string $publicId, PaperSubmissionReadiness $readiness): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        $paper = Paper::query()->ownedBy($actor)->where('public_id', $publicId)->firstOrFail();
        Gate::authorize('submit', $paper);

        $issues = $readiness->issues($paper);
        if ($issues !== []) {
            $this->addError('paper', implode(' ', $issues));

            return;
        }

        $this->resetValidation('paper');
        $this->confirmingPublicId = $paper->public_id;
        $this->confirmingReference = $paper->reference ?? '';
        $this->submitModalOpen = true;
    }

    public function cancelSubmit(): void
    {
        $this->submitModalOpen = false;
        $this->confirmingPublicId = null;
        $this->confirmingReference = '';
        $this->resetValidation('paper');
    }

    public function confirmSubmit(SubmitPaper $submitPaper): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        if (! $this->submitModalOpen || $this->confirmingPublicId === null) {
            return;
        }

        $paper = $submitPaper->handle($actor, $this->confirmingPublicId);

        $this->cancelSubmit();
        session()->flash('success', "Paper {$paper->reference} submitted successfully. Your paper ID remains unchanged.");
    }

    public function render(PaperSubmissionReadiness $readiness): View
    {
        Gate::authorize('viewAny', Paper::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $query = Paper::query()->ownedBy($user);
        $papers = $query
            ->with(['conferenceTrack', 'authors'])
            ->when(trim($this->search) !== '', function (Builder $builder): void {
                $term = '%'.trim($this->search).'%';
                $builder->where(fn (Builder $filter) => $filter
                    ->where('title', 'like', $term)
                    ->orWhere('reference', 'like', $term));
            })
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate(10);

        $readyIds = [];
        foreach ($papers as $paper) {
            if ($paper->status === PaperStatus::Draft && $readiness->ready($paper)) {
                $readyIds[] = $paper->public_id;
            }
        }

        return view('livewire.conference.my-papers', [
            'papers' => $papers,
            'readyPaperIds' => $readyIds,
            'totalPapers' => Paper::query()->ownedBy($user)->count(),
            'draftCount' => Paper::query()->ownedBy($user)->where('status', PaperStatus::Draft->value)->count(),
            'submittedCount' => Paper::query()->ownedBy($user)->where('status', PaperStatus::Submitted->value)->count(),
        ]);
    }
}
