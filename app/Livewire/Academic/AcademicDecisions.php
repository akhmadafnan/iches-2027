<?php

namespace App\Livewire\Academic;

use App\Actions\Academic\CorrectAcademicDecision;
use App\Actions\Academic\PublishAcademicDecisions;
use App\Actions\Academic\RetryDecisionNotification;
use App\Enums\PaperReviewStatus;
use App\Enums\PaperStatus;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\PaperDecision;
use App\Models\User;
use App\Services\Academic\ReadyAcademicDecisions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Academic Decisions | ICHES 2027')]
final class AcademicDecisions extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'ready';

    public string $search = '';

    public string $trackFilter = '';

    /** @var list<string> */
    public array $selectedIds = [];

    /** @var array<string, array{outcome: string, reason: string}> */
    public array $overrideMap = [];

    public bool $overrideModalOpen = false;

    public ?string $overridePublicId = null;

    public string $overrideReference = '';

    public string $overrideTarget = 'accepted';

    public string $overrideReason = '';

    public bool $publishModalOpen = false;

    public bool $publishingAllReady = false;

    public string $allReadyConfirmation = '';

    /** @var list<string> */
    public array $confirmIds = [];

    /** @var list<string> */
    public array $confirmReferences = [];

    /** @var array{total: int, accepted: int, rejected: int, overridden: int, comments: int} */
    public array $preview = ['total' => 0, 'accepted' => 0, 'rejected' => 0, 'overridden' => 0, 'comments' => 0];

    /** @var array{published: int, skipped: int, failed: int, notifications: int}|null */
    public ?array $lastResult = null;

    public bool $correctionModalOpen = false;

    public ?string $correctionPublicId = null;

    public string $correctionReference = '';

    public string $correctionTarget = 'accepted';

    public string $correctionReason = '';

    public int $correctionVersion = 0;

    public function mount(): void
    {
        Gate::authorize('publish-decisions');
        if (! in_array($this->tab, ['ready', 'awaiting', 'published'], true)) {
            $this->tab = 'ready';
        }
    }

    public function selectTab(string $tab): void
    {
        Gate::authorize('publish-decisions');
        abort_unless(in_array($tab, ['ready', 'awaiting', 'published'], true), 404);
        $this->tab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTrackFilter(): void
    {
        $this->resetPage();
    }

    private function readyPaper(string $publicId, ReadyAcademicDecisions $ready): Paper
    {
        return $ready->query()->where('public_id', $publicId)->with(['reviews.claim'])->firstOrFail();
    }

    private function reviewerOutcome(Paper $paper): string
    {
        $review = $paper->reviews->first(fn ($item) => $item->status === PaperReviewStatus::Submitted
            && $item->claim?->active_paper_id === $paper->id);

        return $review?->recommendation?->value === 'accept' ? 'accepted' : 'rejected';
    }

    /**
     * @param  Builder<Paper>  $query
     * @return Builder<Paper>
     */
    private function applyReadyFilters(Builder $query): Builder
    {
        $term = trim($this->search);
        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('title', 'like', '%'.$term.'%')
                    ->orWhere('reference', 'like', '%'.$term.'%');
            });
        }

        if ($this->trackFilter !== '') {
            $query->where('conference_track_id', $this->trackFilter);
        }

        return $query;
    }

    public function toggleCurrentPage(ReadyAcademicDecisions $ready): void
    {
        Gate::authorize('publish-decisions');
        abort_unless($this->tab === 'ready', 403);

        $pageIds = $this->applyReadyFilters($ready->query())
            ->orderByDesc('updated_at')
            ->offset(($this->getPage() - 1) * 10)
            ->limit(10)
            ->pluck('public_id')
            ->all();

        if ($pageIds === []) {
            return;
        }

        $allSelected = count(array_diff($pageIds, $this->selectedIds)) === 0;
        $this->selectedIds = $allSelected
            ? array_values(array_diff($this->selectedIds, $pageIds))
            : array_values(array_unique(array_merge($this->selectedIds, $pageIds)));
    }

    public function clearSelection(): void
    {
        Gate::authorize('publish-decisions');
        $this->selectedIds = [];
    }

    public function toggleSelected(string $publicId, ReadyAcademicDecisions $ready): void
    {
        Gate::authorize('publish-decisions');
        $this->readyPaper($publicId, $ready);
        if (in_array($publicId, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$publicId]));
        } else {
            $this->selectedIds[] = $publicId;
        }
    }

    public function openOverride(string $publicId, ReadyAcademicDecisions $ready): void
    {
        Gate::authorize('publish-decisions');
        $paper = $this->readyPaper($publicId, $ready);
        $this->overridePublicId = $publicId;
        $this->overrideReference = $paper->reference ?? '';
        $this->overrideTarget = $this->overrideMap[$publicId]['outcome']
            ?? ($this->reviewerOutcome($paper) === 'accepted' ? 'rejected' : 'accepted');
        $this->overrideReason = $this->overrideMap[$publicId]['reason'] ?? '';
        $this->resetValidation('override');
        $this->overrideModalOpen = true;
    }

    public function saveOverride(ReadyAcademicDecisions $ready): void
    {
        Gate::authorize('publish-decisions');
        abort_if($this->overridePublicId === null, 404);
        $paper = $this->readyPaper($this->overridePublicId, $ready);
        $reason = trim($this->overrideReason);

        if (! in_array($this->overrideTarget, ['accepted', 'rejected'], true) ||
            $this->overrideTarget === $this->reviewerOutcome($paper) ||
            mb_strlen($reason) < 10 || mb_strlen($reason) > 2000) {
            throw ValidationException::withMessages(['override' => 'Choose the opposite outcome and give an internal reason (10–2,000 characters).']);
        }

        $this->overrideMap[$this->overridePublicId] = [
            'outcome' => $this->overrideTarget,
            'reason' => $reason,
        ];
        $this->overrideModalOpen = false;
        $this->overridePublicId = null;
    }

    public function removeOverride(string $publicId): void
    {
        Gate::authorize('publish-decisions');
        unset($this->overrideMap[$publicId]);
    }

    public function cancelOverride(): void
    {
        $this->overrideModalOpen = false;
        $this->overridePublicId = null;
        $this->resetValidation('override');
    }

    public function askPublish(bool $allReady, ReadyAcademicDecisions $ready): void
    {
        Gate::authorize('publish-decisions');
        $this->resetValidation('publish');

        $query = $ready->query();
        if (! $allReady) {
            $query->whereIn('public_id', array_values(array_unique($this->selectedIds)));
        }

        $ids = $query->orderBy('id')->limit(1001)->pluck('public_id')->all();

        if (count($ids) > 1000) {
            throw ValidationException::withMessages(['publish' => 'Limit one batch to 1,000 ready papers.']);
        }
        if ($ids === []) {
            throw ValidationException::withMessages(['publish' => 'No eligible papers selected. Choose a ready paper or Publish All Ready.']);
        }

        $papers = $ready->query()->whereIn('public_id', $ids)->with('reviews.claim')->get();
        $counts = ['total' => 0, 'accepted' => 0, 'rejected' => 0, 'overridden' => 0, 'comments' => 0];

        foreach ($papers as $paper) {
            $counts['total']++;
            $override = $this->overrideMap[$paper->public_id] ?? null;
            if ($override !== null && $override['outcome'] !== $this->reviewerOutcome($paper)) {
                if (mb_strlen(trim($override['reason'])) < 10) {
                    throw ValidationException::withMessages(['publish' => 'An override requires at least 10 characters of internal reason.']);
                }
                $counts['overridden']++;
            }
            $outcome = $override['outcome'] ?? $this->reviewerOutcome($paper);
            $counts[$outcome === 'accepted' ? 'accepted' : 'rejected']++;
            // All finalized General Comments are included automatically, without per-paper toggles.
            $review = $paper->reviews->first(fn ($item) => $item->status === PaperReviewStatus::Submitted
                && $item->claim?->active_paper_id === $paper->id);
            if (filled($review?->general_comments)) {
                $counts['comments']++;
            }
        }

        $this->confirmIds = array_values(array_map('strval', $ids));
        $this->confirmReferences = array_values($papers->sortBy('id')->take(20)->pluck('reference')->map(fn ($value) => (string) $value)->values()->all());
        $this->preview = $counts;
        $this->publishingAllReady = $allReady;
        $this->allReadyConfirmation = '';
        $this->publishModalOpen = true;
    }

    public function cancelPublish(): void
    {
        $this->publishModalOpen = false;
        $this->publishingAllReady = false;
        $this->allReadyConfirmation = '';
        $this->confirmIds = [];
        $this->confirmReferences = [];
        $this->resetValidation('publish');
    }

    public function confirmPublish(PublishAcademicDecisions $action): void
    {
        Gate::authorize('publish-decisions');
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);
        if (! $this->publishModalOpen || $this->confirmIds === []) {
            return;
        }

        if ($this->publishingAllReady) {
            if ($this->allReadyConfirmation !== 'PUBLISH ALL READY') {
                throw ValidationException::withMessages(['publish' => 'Type PUBLISH ALL READY exactly to confirm every eligible paper.']);
            }

        }

        $result = $action->handle($actor, $this->confirmIds, $this->overrideMap);
        $this->lastResult = $result;
        $this->cancelPublish();
        $this->selectedIds = [];
        $this->overrideMap = [];
        $this->resetPage();
    }

    public function openCorrection(string $publicId): void
    {
        Gate::authorize('publish-decisions');
        $paper = Paper::query()->where('public_id', $publicId)->with('decision')->firstOrFail();
        abort_if($paper->decision === null, 404);

        $this->correctionPublicId = $publicId;
        $this->correctionReference = $paper->reference ?? '';
        $this->correctionTarget = $paper->decision->outcome === 'accepted' ? 'rejected' : 'accepted';
        $this->correctionVersion = $paper->decision->version;
        $this->correctionReason = '';
        $this->resetValidation('correction');
        $this->correctionModalOpen = true;
    }

    public function cancelCorrection(): void
    {
        $this->correctionModalOpen = false;
        $this->correctionPublicId = null;
        $this->resetValidation('correction');
    }

    public function confirmCorrection(CorrectAcademicDecision $action): void
    {
        Gate::authorize('publish-decisions');
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);
        if (! $this->correctionModalOpen || $this->correctionPublicId === null) {
            return;
        }

        $action->handle($actor, $this->correctionPublicId, $this->correctionTarget,
            $this->correctionReason, $this->correctionVersion);

        $this->cancelCorrection();
        session()->flash('success', 'Published decision corrected and a personal correction notice queued.');
    }

    public function retryNotification(string $notificationId, RetryDecisionNotification $action): void
    {
        Gate::authorize('publish-decisions');
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);
        $action->handle($actor, $notificationId);
        session()->flash('success', 'Personal notification requeued. Sending depends on the queue worker.');
    }

    public function render(ReadyAcademicDecisions $ready): View
    {
        Gate::authorize('publish-decisions');

        $readyQuery = $ready->query();
        $awaitingQuery = Paper::query()
            ->where('status', PaperStatus::Submitted->value)
            ->whereDoesntHave('decision')
            ->whereNotIn('id', $ready->query()->select('papers.id'));
        $publishedQuery = Paper::query()->whereHas('decision');

        $query = match ($this->tab) {
            'awaiting' => $awaitingQuery,
            'published' => $publishedQuery,
            default => $readyQuery,
        };

        $term = trim($this->search);
        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('title', 'like', '%'.$term.'%')
                    ->orWhere('reference', 'like', '%'.$term.'%');
            });
        }
        if ($this->trackFilter !== '') {
            $query->where('conference_track_id', $this->trackFilter);
        }

        return view('livewire.academic.academic-decisions', [
            'papers' => $query->with(['conferenceTrack', 'reviews.claim', 'decision.events', 'decisionNotifications'])
                ->orderByDesc('updated_at')->paginate(10),
            'tracks' => ConferenceTrack::query()->orderBy('id')->get(),
            'readyCount' => $ready->query()->count(),
            'awaitingCount' => Paper::query()->where('status', PaperStatus::Submitted->value)
                ->whereDoesntHave('decision')->whereNotIn('id', $ready->query()->select('papers.id'))->count(),
            'publishedCount' => PaperDecision::query()->count(),
        ]);
    }
}
