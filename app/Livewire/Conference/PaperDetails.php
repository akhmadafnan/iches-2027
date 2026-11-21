<?php

namespace App\Livewire\Conference;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Paper Submission | ICHES 2027')]
class PaperDetails extends Component
{
    public string $paperPublicId;

    public function mount(Paper $paper): void
    {
        Gate::authorize('view', $paper);
        $this->paperPublicId = $paper->public_id;
    }

    public function render(): View
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $paper = Paper::query()
            ->ownedBy($user)
            ->where('public_id', $this->paperPublicId)
            ->with(['authors', 'conferenceTrack', 'statusHistories'])
            ->firstOrFail();

        Gate::authorize('view', $paper);

        return view('livewire.conference.paper-details', compact('paper'));
    }
}
