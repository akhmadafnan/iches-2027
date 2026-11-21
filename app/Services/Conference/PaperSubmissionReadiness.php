<?php

namespace App\Services\Conference;

use App\Models\Paper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class PaperSubmissionReadiness
{
    /**
     * Only saved server-side metadata qualifies. Draft may be incomplete while editable.
     *
     * @return list<string>
     */
    public function issues(Paper $paper): array
    {
        $paper->loadMissing('authors', 'conferenceTrack');

        $issues = [];

        $title = trim($paper->title);
        if (Str::length($title) < 5 || Str::length($title) > 255) {
            $issues[] = 'A valid paper title is required.';
        }

        if ($paper->conferenceTrack === null || ! $paper->conferenceTrack->is_active) {
            $issues[] = 'Select an active conference track.';
        }

        $abstract = trim($paper->abstract_text ?? '');
        if ($abstract === '' || Str::length($abstract) > 10000) {
            $issues[] = 'Abstract text is required.';
        }

        $rawKeywords = $paper->getRawOriginal('keywords');
        $keywords = is_string($rawKeywords) ? json_decode($rawKeywords, true) : null;
        if (! is_array($keywords) || count($keywords) < 1 || count($keywords) > 15) {
            $issues[] = 'Add at least one valid keyword.';
        } else {
            $seen = [];
            foreach ($keywords as $keyword) {
                if (! is_string($keyword)) {
                    $issues[] = 'Each keyword must be valid.';
                    break;
                }
                $clean = Str::squish($keyword);
                $fold = mb_strtolower($clean);
                if (mb_strlen($clean) < 2 || mb_strlen($clean) > 80 || isset($seen[$fold])) {
                    $issues[] = 'Each keyword must be unique and 2–80 characters.';
                    break;
                }
                $seen[$fold] = true;
            }
        }

        if ($paper->authors->isEmpty() || $paper->authors->where('is_corresponding', true)->count() !== 1) {
            $issues[] = 'A corresponding author and valid author list are required.';
        }

        $emails = [];
        foreach ($paper->authors as $author) {
            $email = mb_strtolower(trim($author->email));
            $source = $author->affiliation_source;

            if (mb_strlen(trim($author->name)) < 2
                || ! filter_var($email, FILTER_VALIDATE_EMAIL)
                || isset($emails[$email])
                || mb_strlen(trim($author->institution ?? '')) < 3
                || ! in_array($source, ['ror', 'manual'], true)
                || ($source === 'ror' && ! preg_match('~^https://ror\.org/[0-9a-z]{9}$~', $author->ror_id ?? ''))
                || ($source === 'manual' && filled($author->ror_id))) {
                $issues[] = 'Complete all author names, emails and verified or manual affiliations.';
                break;
            }
            $emails[$email] = true;
        }

        $path = $paper->abstract_pdf_path;
        $namespace = "paper-abstracts/{$paper->owner_id}/{$paper->public_id}/";

        if (! is_string($path)
            || ! str_starts_with($path, $namespace)
            || ! str_ends_with(strtolower($path), '.pdf')
            || $paper->abstract_pdf_size === null
            || $paper->abstract_pdf_size < 1
            || $paper->abstract_pdf_size > 10 * 1024 * 1024
            || blank($paper->abstract_pdf_original_name)
            || $paper->abstract_pdf_uploaded_at === null
            || ! Storage::disk('local')->exists($path)) {
            $issues[] = 'Upload and save a valid private Abstract PDF.';
        }

        return $issues;
    }

    public function ready(Paper $paper): bool
    {
        return $this->issues($paper) === [];
    }
}
