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
     * Staged author data: only saved to the paper when the outer Save Draft action runs.
     *
     * @var list<array{key: string, name: string, email: string, institution: string, country: string, ror_id: string, affiliation_source: string, institution_query: string}>
     */
    public array $authors = [];

    public int $correspondingIndex = 0;

    public bool $authorModalOpen = false;

    public ?int $editingAuthorIndex = null;

    /**
     * Temporary modal form; Cancel discards these changes.
     *
     * @var array<string, string>
     */
    public array $authorDraft = [];

    public bool $authorDraftCorresponding = false;

    /** @var list<array{id: string, name: string, aliases: string, location: string}> */
    public array $affiliationResults = [];

    public string $affiliationError = '';

    public bool $affiliationSearched = false;

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
        $this->resetAuthorModal();

        $this->editingAuthorIndex = null;
        $this->authorDraft = $this->newAuthor();
        $this->authorDraftCorresponding = false;
        $this->authorModalOpen = true;
    }

    public function editAuthor(int $index): void
    {
        if (! isset($this->authors[$index])) {
            return;
        }

        $this->resetAuthorModal();

        $this->editingAuthorIndex = $index;
        $this->authorDraft = $this->authors[$index];
        $this->authorDraftCorresponding = $this->correspondingIndex === $index;
        $this->authorModalOpen = true;
    }

    public function cancelAuthor(): void
    {
        $this->resetAuthorModal();
    }

    public function saveAuthor(): void
    {
        $data = $this->validate([
            'authorDraft.name' => ['required', 'string', 'min:2', 'max:150'],
            'authorDraft.email' => ['required', 'email:rfc', 'max:254'],
            'authorDraft.institution' => ['nullable', 'string', 'max:255'],
            'authorDraft.ror_id' => ['nullable', 'string', 'max:48'],
            'authorDraft.affiliation_source' => ['required', Rule::in(['ror', 'manual'])],
            'authorDraft.country' => ['nullable', 'string', 'max:120'],
            'authorDraftCorresponding' => ['required', 'boolean'],
        ]);

        $email = mb_strtolower(trim($data['authorDraft']['email']));

        foreach ($this->authors as $index => $author) {
            if ($index !== $this->editingAuthorIndex && mb_strtolower(trim($author['email'])) === $email) {
                throw ValidationException::withMessages([
                    'authorDraft.email' => 'This email is already assigned to another author.',
                ]);
            }
        }

        $source = $data['authorDraft']['affiliation_source'];

        if ($source === 'ror') {
            try {
                $organization = app(RorOrganizationService::class)->resolve($data['authorDraft']['ror_id'] ?? '');
            } catch (InvalidArgumentException|RequestException|ConnectionException $exception) {
                throw ValidationException::withMessages([
                    'authorDraft.ror_id' => 'ROR could not verify this organization. Please select a valid result.',
                ]);
            }

            if ($organization === null) {
                throw ValidationException::withMessages([
                    'authorDraft.ror_id' => 'The selected organization is not active in ROR.',
                ]);
            }

            $institution = $organization['name'];
            $rorId = $organization['id'];
        } else {
            $institution = trim($data['authorDraft']['institution'] ?? '');
            $rorId = '';

            if (mb_strlen($institution) < 3 || mb_strlen($institution) > 255) {
                throw ValidationException::withMessages([
                    'authorDraft.institution' => 'Enter the full name of the unlisted institution (3–255 characters).',
                ]);
            }
        }

        if ($this->editingAuthorIndex !== null
            && $this->editingAuthorIndex === $this->correspondingIndex
            && ! $this->authorDraftCorresponding) {
            throw ValidationException::withMessages([
                'authorDraftCorresponding' => 'To change the corresponding author, choose another contributor in the directory.',
            ]);
        }

        $record = [
            'key' => $this->authorDraft['key'] ?? (string) Str::ulid(),
            'name' => trim($data['authorDraft']['name']),
            'email' => $email,
            'institution' => $institution,
            'country' => filled($data['authorDraft']['country'] ?? null) ? trim($data['authorDraft']['country']) : '',
            'ror_id' => $rorId,
            'affiliation_source' => $source,
            'institution_query' => $institution,
        ];

        if ($this->editingAuthorIndex === null) {
            $this->authors[] = $record;
            $index = count($this->authors) - 1;
        } else {
            $index = $this->editingAuthorIndex;
            $this->authors[$index] = $record;
        }

        if ($this->authorDraftCorresponding) {
            $this->correspondingIndex = $index;
        }

        $this->resetAuthorModal();
        $this->resetValidation();
    }

    public function setCorrespondingAuthor(int $index): void
    {
        if (! isset($this->authors[$index])) {
            return;
        }

        $this->correspondingIndex = $index;
        $this->resetValidation('correspondingIndex');
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

        $this->resetValidation();
    }

    /**
     * Livewire nested update: only the modal's ROR query triggers search.
     */
    public function updatedAuthorDraft(mixed $value, string $key): void
    {
        if ($key !== 'institution_query') {
            return;
        }

        $this->authorDraft['ror_id'] = '';
        $this->authorDraft['institution'] = '';
        $this->authorDraft['affiliation_source'] = '';
        $this->affiliationResults = [];
        $this->affiliationError = '';
        $this->affiliationSearched = false;

        $term = trim(is_string($value) ? $value : '');

        if (mb_strlen($term) < 2) {
            return;
        }

        $this->affiliationSearched = true;

        try {
            $this->affiliationResults = app(RorOrganizationService::class)->search($term);
        } catch (RequestException|ConnectionException $exception) {
            $this->affiliationError = 'ROR search is temporarily unavailable. Try again or select the unlisted-institution option.';
        }
    }

    public function selectInstitution(string $rorId): void
    {
        if (! $this->authorModalOpen) {
            return;
        }

        try {
            $organization = app(RorOrganizationService::class)->resolve($rorId);
        } catch (InvalidArgumentException|RequestException|ConnectionException $exception) {
            $this->affiliationError = 'Unable to verify this ROR organization. Please try again.';

            return;
        }

        if ($organization === null) {
            $this->affiliationError = 'The selected ROR organization is not active.';

            return;
        }

        $this->authorDraft['institution'] = $organization['name'];
        $this->authorDraft['institution_query'] = $organization['name'];
        $this->authorDraft['ror_id'] = $organization['id'];
        $this->authorDraft['affiliation_source'] = 'ror';
        $this->affiliationResults = [];
        $this->affiliationError = '';
        $this->affiliationSearched = false;
        $this->resetValidation();
    }

    public function useManualAffiliation(): void
    {
        if (! $this->authorModalOpen) {
            return;
        }

        $this->authorDraft['institution'] = $this->authorDraft['institution'] ?: trim($this->authorDraft['institution_query']);
        $this->authorDraft['ror_id'] = '';
        $this->authorDraft['affiliation_source'] = 'manual';
        $this->affiliationResults = [];
        $this->affiliationError = '';
        $this->affiliationSearched = false;
        $this->resetValidation();
    }

    public function useRorAffiliation(): void
    {
        if (! $this->authorModalOpen) {
            return;
        }

        $this->authorDraft['institution_query'] = $this->authorDraft['institution_query'] ?: $this->authorDraft['institution'];
        $this->authorDraft['institution'] = '';
        $this->authorDraft['ror_id'] = '';
        $this->authorDraft['affiliation_source'] = '';
        $this->affiliationResults = [];
        $this->affiliationError = '';
        $this->affiliationSearched = false;
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

    /**
     * @return array<string, string>
     */
    private function newAuthor(): array
    {
        return [
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

    private function resetAuthorModal(): void
    {
        $this->authorModalOpen = false;
        $this->editingAuthorIndex = null;
        $this->authorDraft = [];
        $this->authorDraftCorresponding = false;
        $this->affiliationResults = [];
        $this->affiliationError = '';
        $this->affiliationSearched = false;
        $this->resetValidation();
    }
}
