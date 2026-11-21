<?php

namespace App\Livewire\Reviewer;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Review Paper Preview | ICHES 2027')]
class PaperPreview extends Component
{
    public string $paperPublicId;

    public function mount(Paper $paper): void
    {
        Gate::authorize('review-papers');

        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $this->abortUnlessEligible($paper, $reviewer);
        $this->paperPublicId = $paper->public_id;
    }

    public function render(): View
    {
        Gate::authorize('review-papers');

        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);

        $paper = Paper::query()
            ->where('public_id', $this->paperPublicId)
            ->where('status', PaperStatus::Submitted->value)
            ->where('owner_id', '!=', $reviewer->id)
            ->with('conferenceTrack')
            ->firstOrFail();

        return view('livewire.reviewer.paper-preview', compact('paper'));
    }

    private function abortUnlessEligible(Paper $paper, User $reviewer): void
    {
        abort_unless($paper->status === PaperStatus::Submitted && $paper->owner_id !== $reviewer->id, 404);
    }
}
