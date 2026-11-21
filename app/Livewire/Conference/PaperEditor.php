<?php

namespace App\Livewire\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
     * @var list<array{key: string, name: string, email: string, institution: string, country: string}>
     */
    public array $authors = [];

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
            'institution' => $actor->institution ?? '',
            'country' => $actor->country ?? '',
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
}
