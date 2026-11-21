<?php

namespace App\Http\Controllers;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReviewerAbstractDownloadController
{
    public function __invoke(Paper $paper): StreamedResponse
    {
        Gate::authorize('review-papers');

        $reviewer = Auth::user();
        abort_unless($reviewer instanceof User, 403);
        abort_unless($paper->status === PaperStatus::Submitted && $paper->owner_id !== $reviewer->id, 404);

        $path = $paper->abstract_pdf_path;
        $directory = "paper-abstracts/{$paper->owner_id}/{$paper->public_id}/";

        abort_unless(
            is_string($path)
                && str_starts_with($path, $directory)
                && Storage::disk('local')->exists($path),
            404,
        );

        return Storage::disk('local')->download($path, "ICHES2027-Review-{$paper->reference}.pdf", [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
