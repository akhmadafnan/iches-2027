<?php

namespace App\Actions\Conference;

use App\Enums\PaperStatus;
use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use App\Services\RorOrganizationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class SavePaperDraft
{
    /**
     * @param  array{title: string, conference_track_id: int}  $input
     * @param  list<array{name: string, email: string, institution?: string|null, country?: string|null, ror_id?: string|null, affiliation_source?: string|null}>  $authors
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

        // Resolve the authoritative ROR record on the server. A browser-provided
        // organization name is never accepted as a ROR-verified affiliation.
        $normalizedAuthors = $this->normalizeAffiliations($authors);

        return DB::transaction(function () use ($actor, $existing, $input, $normalizedAuthors, $correspondingIndex, $track): Paper {
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

            foreach ($normalizedAuthors as $position => $author) {
                $paper->authors()->create([
                    'position' => $position + 1,
                    'name' => trim($author['name']),
                    'email' => mb_strtolower(trim($author['email'])),
                    'institution' => $author['institution'],
                    'ror_id' => $author['ror_id'],
                    'affiliation_source' => $author['affiliation_source'],
                    'country' => filled($author['country'] ?? null) ? trim($author['country']) : null,
                    'is_corresponding' => $position === $correspondingIndex,
                ]);
            }

            return $paper->load('authors', 'conferenceTrack');
        });
    }

    /**
     * @param  list<array{name: string, email: string, institution?: string|null, country?: string|null, ror_id?: string|null, affiliation_source?: string|null}>  $authors
     * @return list<array{name: string, email: string, institution: string, country?: string|null, ror_id: string|null, affiliation_source: string}>
     */
    private function normalizeAffiliations(array $authors): array
    {
        $ror = app(RorOrganizationService::class);
        $normalized = [];

        foreach ($authors as $index => $author) {
            $source = $author['affiliation_source'] ?? null;

            if ($source === 'ror') {
                try {
                    $organization = $ror->resolve($author['ror_id'] ?? '');
                } catch (InvalidArgumentException $exception) {
                    throw ValidationException::withMessages([
                        "authors.{$index}.ror_id" => 'Please select a valid ROR organization from search results.',
                    ]);
                } catch (RequestException|ConnectionException $exception) {
                    throw ValidationException::withMessages([
                        "authors.{$index}.ror_id" => 'ROR is temporarily unavailable. Please try again; your draft has not been saved.',
                    ]);
                }

                if ($organization === null) {
                    throw ValidationException::withMessages([
                        "authors.{$index}.ror_id" => 'The selected organization is not active in ROR.',
                    ]);
                }

                $normalized[] = [
                    ...$author,
                    'institution' => $organization['name'],
                    'ror_id' => $organization['id'],
                    'affiliation_source' => 'ror',
                ];

                continue;
            }

            if ($source === 'manual') {
                $name = trim($author['institution'] ?? '');

                if (mb_strlen($name) < 3 || mb_strlen($name) > 255) {
                    throw ValidationException::withMessages([
                        "authors.{$index}.institution" => 'Enter the full institution name (3–255 characters) for a manual affiliation.',
                    ]);
                }

                $normalized[] = [
                    ...$author,
                    'institution' => $name,
                    'ror_id' => null,
                    'affiliation_source' => 'manual',
                ];

                continue;
            }

            throw ValidationException::withMessages([
                "authors.{$index}.affiliation_source" => 'Select an organization from ROR or explicitly choose the unlisted-institution option.',
            ]);
        }

        return $normalized;
    }
}
