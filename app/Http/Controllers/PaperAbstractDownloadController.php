<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PaperAbstractDownloadController
{
    public function __invoke(Paper $paper): StreamedResponse
    {
        Gate::authorize('view', $paper);

        $path = $paper->abstract_pdf_path;
        $directory = "paper-abstracts/{$paper->owner_id}/{$paper->public_id}/";

        abort_unless(
            is_string($path)
                && str_starts_with($path, $directory)
                && Storage::disk('local')->exists($path),
            404,
        );

        $filename = "ICHES2027-Abstract-{$paper->reference}.pdf";

        return Storage::disk('local')->download($path, $filename, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
