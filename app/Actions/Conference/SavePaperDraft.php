<?php

namespace App\Actions\Conference;

use App\Enums\PaperStatus;
use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use App\Services\RorOrganizationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class SavePaperDraft
{
    /**
     * @param  array{title: string, conference_track_id: int}  $input
     * @param  list<array{name: string, email: string, institution?: string|null, country?: string|null, ror_id?: string|null, affiliation_source?: string|null}>  $authors
     * @param  array{abstract_text?: string|null, keywords?: list<string>}  $abstractData
     */
    public function handle(
        User $actor,
        ?Paper $existing,
        array $input,
        array $authors,
        int $correspondingIndex,
        array $abstractData = [],
        ?UploadedFile $abstractPdf = null,
        bool $removeAbstractPdf = false,
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

        // Resolve ROR records on the server rather than trusting browser-provided labels.
        $normalizedAuthors = $this->normalizeAffiliations($authors);
        $validatedAbstract = $abstractData !== [] ? $this->normalizeAbstract($abstractData) : null;

        if ($abstractPdf !== null) {
            $this->validatePdf($abstractPdf);
        }

        $newFilePath = null;
        $previousFilePath = null;

        try {
            $saved = DB::transaction(function () use (
                $actor, $existing, $input, $normalizedAuthors, $correspondingIndex,
                $track, $validatedAbstract, $abstractPdf, $removeAbstractPdf,
                &$newFilePath, &$previousFilePath
            ): Paper {
                if ($existing !== null) {
                    $paper = Paper::query()->whereKey($existing->id)->lockForUpdate()->firstOrFail();
                    Gate::forUser($actor)->authorize('update', $paper);
                } else {
                    // Serialize first-paper creation for the same account across concurrent requests.
                    User::query()->whereKey($actor->id)->lockForUpdate()->firstOrFail();

                    if (Paper::query()->where('owner_id', $actor->id)->exists()) {
                        throw ValidationException::withMessages([
                            'paper' => 'You already own a paper. Continue your existing submission.',
                        ]);
                    }

                    $paper = new Paper;
                    $paper->owner_id = $actor->id;
                    $paper->public_id = (string) Str::ulid();
                    $paper->status = PaperStatus::Draft;
                }

                $paper->title = trim($input['title']);
                $paper->conference_track_id = $track->id;

                if ($validatedAbstract !== null) {
                    $paper->abstract_text = $validatedAbstract['abstract_text'];
                    $paper->keywords = $validatedAbstract['keywords'];
                }

                $paper->save();

                if ($paper->reference === null) {
                    $paper->reference = sprintf('ICH27-P%06d', $paper->id);
                    $paper->save();
                }

                if ($abstractPdf !== null || $removeAbstractPdf) {
                    $previousFilePath = $paper->abstract_pdf_path;

                    if ($abstractPdf !== null) {
                        $directory = "paper-abstracts/{$actor->id}/{$paper->public_id}";
                        $newFilePath = $abstractPdf->storeAs($directory, Str::uuid().'.pdf', 'local');

                        if (! is_string($newFilePath) || ! Storage::disk('local')->exists($newFilePath)) {
                            throw new RuntimeException('The private abstract PDF could not be stored.');
                        }

                        $paper->abstract_pdf_path = $newFilePath;
                        $paper->abstract_pdf_original_name = $this->safeOriginalName($abstractPdf);
                        $paper->abstract_pdf_size = $abstractPdf->getSize();
                        $paper->abstract_pdf_uploaded_at = Carbon::now();
                    } else {
                        $paper->abstract_pdf_path = null;
                        $paper->abstract_pdf_original_name = null;
                        $paper->abstract_pdf_size = null;
                        $paper->abstract_pdf_uploaded_at = null;
                    }

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
        } catch (Throwable $exception) {
            // Database rollback does not roll back filesystem writes.
            if (is_string($newFilePath)) {
                Storage::disk('local')->delete($newFilePath);
            }

            if ($existing === null && $exception instanceof UniqueConstraintViolationException
                && Paper::query()->where('owner_id', $actor->id)->exists()) {
                throw ValidationException::withMessages([
                    'paper' => 'You already own a paper. Continue your existing submission.',
                ]);
            }

            throw $exception;
        }

        // Delete the superseded private file only after the new database state commits.
        if (is_string($previousFilePath) && $previousFilePath !== $newFilePath) {
            Storage::disk('local')->delete($previousFilePath);
        }

        return $saved;
    }

    /**
     * @param  array{abstract_text?: string|null, keywords?: list<string>}  $data
     * @return array{abstract_text: string|null, keywords: list<string>}
     */
    private function normalizeAbstract(array $data): array
    {
        $validated = Validator::make($data, [
            'abstract_text' => ['nullable', 'string', 'max:10000'],
            'keywords' => ['sometimes', 'array', 'max:15'],
            'keywords.*' => ['required', 'string', 'min:2', 'max:80'],
        ])->validate();

        $keywords = [];
        $seen = [];

        foreach ($validated['keywords'] ?? [] as $value) {
            $keyword = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
            $folded = mb_strtolower($keyword);

            if (in_array($folded, $seen, true)) {
                throw ValidationException::withMessages([
                    'keywords' => 'Each keyword may only be added once.',
                ]);
            }

            $keywords[] = $keyword;
            $seen[] = $folded;
        }

        $text = trim($validated['abstract_text'] ?? '');

        return ['abstract_text' => $text === '' ? null : $text, 'keywords' => $keywords];
    }

    private function validatePdf(UploadedFile $file): void
    {
        Validator::make(['abstractPdfUpload' => $file], [
            'abstractPdfUpload' => ['required', 'file', File::types(['pdf'])->max(10240)],
        ])->validate();

        if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
            throw ValidationException::withMessages([
                'abstractPdfUpload' => 'The file must use the .pdf extension.',
            ]);
        }

        // Detect a PDF header from file contents, not just its extension or browser MIME.
        $stream = fopen($file->getRealPath(), 'rb');

        if ($stream === false) {
            throw ValidationException::withMessages(['abstractPdfUpload' => 'The PDF file could not be read.']);
        }

        try {
            $signature = fread($stream, 5);
        } finally {
            fclose($stream);
        }

        if ($signature !== '%PDF-') {
            throw ValidationException::withMessages([
                'abstractPdfUpload' => 'Upload a genuine PDF file rather than a renamed document.',
            ]);
        }
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $filename = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $filename = preg_replace('/[[:cntrl:]]/u', '', $filename) ?? 'Abstract.pdf';

        return mb_strimwidth($filename, 0, 240, '');
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
