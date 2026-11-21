<?php

namespace App\Livewire\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use App\Services\RorOrganizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Paper Draft | ICHES 2027')]
class PaperEditor extends Component
{
    public ?string $editingPublicId = null;

    public string $paperReference = '';

    public string $title = '';

    public string $conferenceTrackId = '';

    /**
     * @var list<array{key: string, name: string, email: string, institution: string, country: string, ror_id: string, affiliation_source: string, institution_query: string}>
     */
    public array $authors = [];

    /** @var array<int, list<array{id: string, name: string, aliases: string, location: string}>> */
    public array $affiliationResults = [];

    /** @var array<int, string> */
    public array $affiliationErrors = [];

    /** @var array<int, bool> */
    public array $affiliationSearched = [];

    public int $correspondingIndex = 0;

    public function mount(?Paper $paper = null): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        if ($paper !== null) {
            $owned = Paper::query()->ownedBy($actor)->whereKey($paper->id)->with('authors')->firstOrFail();
            Gate::authorize('update', $owned);

            $this->editingPublicId = $owned->public_id;
            $this->paperReference = $owned->reference ?? '';
            $this->title = $owned->title;
            $this->conferenceTrackId = (string) $owned->conference_track_id;
            $this->authors = array_values($owned->authors->map(fn ($author) => [
                'key' => (string) Str::ulid(),
                'name' => $author->name,
                'email' => $author->email,
                'institution' => $author->institution ?? '',
                'country' => $author->country ?? '',
                'ror_id' => $author->ror_id ?? '',
                'affiliation_source' => $author->affiliation_source ?? (filled($author->institution) ? 'legacy' : ''),
                'institution_query' => $author->institution ?? '',
            ])->all());

            $corresponding = $owned->authors->search(fn ($author) => $author->is_corresponding);
            $this->correspondingIndex = $corresponding === false ? 0 : (int) $corresponding;

            return;
        }

        Gate::authorize('create', Paper::class);

        $this->authors = [[
            'key' => (string) Str::ulid(),
            'name' => $actor->name,
            'email' => $actor->email,
            'institution' => '',
            'country' => $actor->country ?? '',
            'ror_id' => '',
            'affiliation_source' => '',
            'institution_query' => $actor->institution ?? '',
        ]];
    }

    public function addAuthor(): void
    {
        $this->authors[] = [
            'key' => (string) Str::ulid(),
            'name' => '',
            'email' => '',
            'institution' => '',
            'country' => '',
            'ror_id' => '',
            'affiliation_source' => '',
            'institution_query' => '',
        ];
    }

    public function removeAuthor(int $index): void
    {
        if (! isset($this->authors[$index]) || count($this->authors) <= 1) {
            return;
        }

        array_splice($this->authors, $index, 1);

        if ($this->correspondingIndex === $index) {
            $this->correspondingIndex = 0;
        } elseif ($this->correspondingIndex > $index) {
            $this->correspondingIndex--;
        }

        $this->clearAffiliationSearch();
        $this->resetValidation();
    }

    public function moveAuthor(int $index, string $direction): void
    {
        if (! in_array($direction, ['up', 'down'], true) || ! isset($this->authors[$index])) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if (! isset($this->authors[$target])) {
            return;
        }

        [$this->authors[$index], $this->authors[$target]] = [$this->authors[$target], $this->authors[$index]];

        if ($this->correspondingIndex === $index) {
            $this->correspondingIndex = $target;
        } elseif ($this->correspondingIndex === $target) {
            $this->correspondingIndex = $index;
        }

        $this->clearAffiliationSearch();
        $this->resetValidation();
    }

    /**
     * Typeahead search is triggered only for an institution query, not every author field.
     * Editing a previously selected label invalidates its ROR identifier.
     */
    public function updatedAuthors(mixed $value, string $key): void
    {
        if (preg_match('/^(\\d+)\\.institution_query$/', $key, $matches) !== 1) {
            return;
        }

        $index = (int) $matches[1];

        if (! isset($this->authors[$index])) {
            return;
        }

        $this->authors[$index]['ror_id'] = '';
        $this->authors[$index]['institution'] = '';
        $this->authors[$index]['affiliation_source'] = '';
        $this->affiliationResults[$index] = [];
        $this->affiliationErrors[$index] = '';
        $this->affiliationSearched[$index] = false;

        $term = trim(is_string($value) ? $value : '');

        if (mb_strlen($term) < 2) {
            return;
        }

        $this->affiliationSearched[$index] = true;

        try {
            $this->affiliationResults[$index] = app(RorOrganizationService::class)->search($term);
        } catch (RequestException|ConnectionException $exception) {
            $this->affiliationErrors[$index] = 'ROR search is temporarily unavailable. Try again or select the unlisted-institution option.';
        }
    }

    public function selectInstitution(int $index, string $rorId): void
    {
        if (! isset($this->authors[$index])) {
            return;
        }

        try {
            $organization = app(RorOrganizationService::class)->resolve($rorId);
        } catch (InvalidArgumentException|RequestException|ConnectionException $exception) {
            $this->affiliationErrors[$index] = 'Unable to verify this ROR organization. Please retry.';

            return;
        }

        if ($organization === null) {
            $this->affiliationErrors[$index] = 'The selected ROR organization is not active.';

            return;
        }

        $this->authors[$index]['institution'] = $organization['name'];
        $this->authors[$index]['institution_query'] = $organization['name'];
        $this->authors[$index]['ror_id'] = $organization['id'];
        $this->authors[$index]['affiliation_source'] = 'ror';
        $this->affiliationResults[$index] = [];
        $this->affiliationErrors[$index] = '';
        $this->resetValidation();
    }

    public function useManualAffiliation(int $index): void
    {
        if (! isset($this->authors[$index])) {
            return;
        }

        $this->authors[$index]['institution'] = $this->authors[$index]['institution'] ?: trim($this->authors[$index]['institution_query']);
        $this->authors[$index]['ror_id'] = '';
        $this->authors[$index]['affiliation_source'] = 'manual';
        $this->affiliationResults[$index] = [];
        $this->affiliationErrors[$index] = '';
        $this->resetValidation();
    }

    public function useRorAffiliation(int $index): void
    {
        if (! isset($this->authors[$index])) {
            return;
        }

        $this->authors[$index]['institution_query'] = $this->authors[$index]['institution_query'] ?: $this->authors[$index]['institution'];
        $this->authors[$index]['institution'] = '';
        $this->authors[$index]['ror_id'] = '';
        $this->authors[$index]['affiliation_source'] = '';
        $this->affiliationResults[$index] = [];
        $this->affiliationErrors[$index] = '';
        $this->resetValidation();
    }

    public function save(SavePaperDraft $saveDraft): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        $paper = $this->editingPublicId !== null
            ? Paper::query()->ownedBy($actor)->where('public_id', $this->editingPublicId)->firstOrFail()
            : null;

        Gate::authorize($paper === null ? 'create' : 'update', $paper ?? Paper::class);

        $data = $this->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'conferenceTrackId' => ['required', 'integer'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*.name' => ['required', 'string', 'min:2', 'max:150'],
            'authors.*.email' => ['required', 'email:rfc', 'max:254', 'distinct:ignore_case'],
            'authors.*.institution' => ['nullable', 'string', 'max:255'],
            'authors.*.ror_id' => ['nullable', 'string', 'max:48'],
            'authors.*.affiliation_source' => ['required', Rule::in(['ror', 'manual'])],
            'authors.*.country' => ['nullable', 'string', 'max:120'],
            'correspondingIndex' => ['required', 'integer', 'min:0'],
        ]);

        if (! array_key_exists($this->correspondingIndex, $data['authors'])) {
            throw ValidationException::withMessages(['correspondingIndex' => 'Please select a corresponding author from the list.']);
        }

        $authors = array_map(static fn (array $author): array => [
            'name' => $author['name'],
            'email' => $author['email'],
            'institution' => $author['institution'] ?? null,
            'ror_id' => $author['ror_id'] ?? null,
            'affiliation_source' => $author['affiliation_source'],
            'country' => $author['country'] ?? null,
        ], array_values($data['authors']));

        $saved = $saveDraft->handle(
            $actor,
            $paper,
            ['title' => $data['title'], 'conference_track_id' => (int) $data['conferenceTrackId']],
            $authors,
            (int) $data['correspondingIndex'],
        );

        session()->flash('success', "Draft {$saved->reference} saved. It has not been submitted for review.");

        $this->redirectRoute('papers.index', navigate: true);
    }

    public function render(): View
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);

        $paper = $this->editingPublicId !== null
            ? Paper::query()->ownedBy($actor)->where('public_id', $this->editingPublicId)->firstOrFail()
            : null;

        Gate::authorize($paper === null ? 'create' : 'update', $paper ?? Paper::class);

        return view('livewire.conference.paper-editor', [
            'tracks' => ConferenceTrack::query()
                ->whereHas('program', fn ($query) => $query->where('code', ProgramCode::Conference->value))
                ->where(fn ($query) => $query->where('is_active', true)
                    ->when($paper !== null, fn ($query) => $query->orWhereKey($paper->conference_track_id)))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name']),
        ]);
    }

    private function clearAffiliationSearch(): void
    {
        $this->affiliationResults = [];
        $this->affiliationErrors = [];
        $this->affiliationSearched = [];
    }
}
