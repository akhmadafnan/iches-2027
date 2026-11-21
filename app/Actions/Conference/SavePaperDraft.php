<?php

namespace App\Actions\Conference;

use App\Enums\PaperStatus;
use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SavePaperDraft
{
    /**
     * @param  array{title: string, conference_track_id: int}  $input
     * @param  list<array{name: string, email: string, institution?: string|null, country?: string|null}>  $authors
     */
    public function handle(
        User $actor,
        ?Paper $existing,
        array $input,
        array $authors,
        int $correspondingIndex,
    ): Paper {
        if ($existing === null) {
            Gate::forUser($actor)->authorize('create', Paper::class);
        } else {
            Gate::forUser($actor)->authorize('update', $existing);
        }

        if ($authors === [] || ! array_key_exists($correspondingIndex, $authors)) {
            throw ValidationException::withMessages(['authors' => 'Provide at least one author and select a corresponding author.']);
        }

        $emails = array_map(static fn (array $author): string => mb_strtolower(trim($author['email'])), $authors);

        if (count($emails) !== count(array_unique($emails))) {
            throw ValidationException::withMessages(['authors' => 'An email address may appear only once in a paper.']);
        }

        $track = ConferenceTrack::query()
            ->whereKey($input['conference_track_id'])
            ->whereHas('program', fn ($query) => $query->where('code', ProgramCode::Conference->value))
            ->first();

        if ($track === null || (! $track->is_active && $existing?->conference_track_id !== $track->id)) {
            throw ValidationException::withMessages(['conferenceTrackId' => 'Select an available conference track.']);
        }

        return DB::transaction(function () use ($actor, $existing, $input, $authors, $correspondingIndex, $track): Paper {
            if ($existing !== null) {
                $paper = Paper::query()->whereKey($existing->id)->lockForUpdate()->firstOrFail();
                Gate::forUser($actor)->authorize('update', $paper);
            } else {
                $paper = new Paper;
                $paper->owner_id = $actor->id;
                $paper->public_id = (string) Str::ulid();
                $paper->status = PaperStatus::Draft;
            }

            $paper->title = trim($input['title']);
            $paper->conference_track_id = $track->id;
            $paper->save();

            if ($paper->reference === null) {
                $paper->reference = sprintf('ICH27-P%06d', $paper->id);
                $paper->save();
            }

            $paper->authors()->delete();

            foreach ($authors as $position => $author) {
                $paper->authors()->create([
                    'position' => $position + 1,
                    'name' => trim($author['name']),
                    'email' => mb_strtolower(trim($author['email'])),
                    'institution' => filled($author['institution'] ?? null) ? trim($author['institution']) : null,
                    'country' => filled($author['country'] ?? null) ? trim($author['country']) : null,
                    'is_corresponding' => $position === $correspondingIndex,
                ]);
            }

            return $paper->load('authors', 'conferenceTrack');
        });
    }
}
