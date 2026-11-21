<?php

namespace App\Actions\Conference;

use App\Enums\PaperStatus;
use App\Models\Paper;
use App\Models\User;
use App\Services\Conference\PaperSubmissionReadiness;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class SubmitPaper
{
    public function __construct(private readonly PaperSubmissionReadiness $readiness) {}

    public function handle(User $actor, string $paperPublicId): Paper
    {
        return DB::transaction(function () use ($actor, $paperPublicId): Paper {
            $paper = Paper::query()
                ->where('public_id', $paperPublicId)
                ->lockForUpdate()
                ->firstOrFail();

            Gate::forUser($actor)->authorize('view', $paper);

            if ($paper->status !== PaperStatus::Draft) {
                throw ValidationException::withMessages(['paper' => 'This paper was already submitted.']);
            }

            Gate::forUser($actor)->authorize('submit', $paper);

            $issues = $this->readiness->issues($paper);
            if ($issues !== []) {
                throw ValidationException::withMessages(['paper' => implode(' ', $issues)]);
            }

            $now = Carbon::now();
            $paper->status = PaperStatus::Submitted;
            $paper->submitted_at = $now;
            $paper->submission_version = $paper->submission_version + 1;
            $paper->save();

            $paper->statusHistories()->create([
                'actor_id' => $actor->id,
                'event' => 'submitted',
                'from_status' => PaperStatus::Draft->value,
                'to_status' => PaperStatus::Submitted->value,
                'submission_version' => $paper->submission_version,
                'occurred_at' => $now,
            ]);

            return $paper;
        });
    }
}
