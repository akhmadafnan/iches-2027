<?php

namespace App\Livewire\Conference;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\User;
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

    public function mount(): void
    {
        Gate::authorize('viewAny', Paper::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        Gate::authorize('viewAny', Paper::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $query = Paper::query()->ownedBy($user);

        return view('livewire.conference.my-papers', [
            'papers' => $query
                ->with('conferenceTrack:id,name')
                ->when(trim($this->search) !== '', function (Builder $builder): void {
                    $term = '%'.trim($this->search).'%';
                    $builder->where(fn (Builder $filter) => $filter
                        ->where('title', 'like', $term)
                        ->orWhere('reference', 'like', $term));
                })
                ->latest('updated_at')
                ->orderByDesc('id')
                ->paginate(10),
            'totalPapers' => Paper::query()->ownedBy($user)->count(),
            'draftCount' => Paper::query()->ownedBy($user)->where('status', PaperStatus::Draft->value)->count(),
        ]);
    }
}
