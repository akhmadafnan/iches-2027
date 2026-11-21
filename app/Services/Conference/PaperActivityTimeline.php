<?php

namespace App\Services\Conference;

use App\Models\Paper;
use Illuminate\Support\Carbon;

final class PaperActivityTimeline
{
    /**
     * Owner-visible, recorded events only. Never expose reviewer identities or
     * private review recommendations before the academic decision is published.
     *
     * @return list<array{label: string, detail: string|null, occurred_at: Carbon}>
     */
    public function forOwner(Paper $paper): array
    {
        $paper->loadMissing(['statusHistories', 'reviewClaims']);

        $events = [];

        if ($paper->created_at !== null) {
            $events[] = [
                'label' => 'Draft created',
                'detail' => null,
                'occurred_at' => $paper->created_at,
            ];
        }

        $submittedEvents = $paper->statusHistories->where('event', 'submitted');

        foreach ($submittedEvents as $history) {
            $events[] = [
                'label' => 'Paper submitted',
                'detail' => "Version {$history->submission_version}",
                'occurred_at' => $history->occurred_at,
            ];
        }

        if ($submittedEvents->isEmpty() && $paper->submitted_at !== null) {
            $events[] = [
                'label' => 'Paper submitted',
                'detail' => $paper->submission_version > 0 ? "Version {$paper->submission_version}" : null,
                'occurred_at' => $paper->submitted_at,
            ];
        }

        foreach ($paper->reviewClaims as $claim) {
            $events[] = [
                'label' => 'Abstract claimed',
                'detail' => 'Assigned for academic review',
                'occurred_at' => $claim->claimed_at,
            ];

            if ($claim->released_at !== null) {
                $events[] = [
                    'label' => 'Reviewer assignment released',
                    'detail' => null,
                    'occurred_at' => $claim->released_at,
                ];
            }
        }

        usort(
            $events,
            fn (array $a, array $b): int => $a['occurred_at']->getTimestamp() <=> $b['occurred_at']->getTimestamp(),
        );

        return $events;
    }
}
