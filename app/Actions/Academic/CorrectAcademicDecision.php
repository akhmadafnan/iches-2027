<?php

namespace App\Actions\Academic;

use App\Enums\PaperStatus;
use App\Jobs\SendDecisionNotification;
use App\Models\DecisionNotification;
use App\Models\Paper;
use App\Models\PaperDecision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CorrectAcademicDecision
{
    public function handle(
        User $actor,
        string $publicId,
        string $newOutcome,
        string $reason,
        int $expectedVersion,
    ): PaperDecision {
        Gate::forUser($actor)->authorize('publish-decisions');

        if (! in_array($newOutcome, ['accepted', 'rejected'], true)) {
            throw ValidationException::withMessages(['correction' => 'Choose Accepted or Rejected.']);
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 2000) {
            throw ValidationException::withMessages(['correction' => 'Correction requires an internal reason of 10–2,000 characters.']);
        }

        return DB::transaction(function () use ($actor, $publicId, $newOutcome, $reason, $expectedVersion): PaperDecision {
            Gate::forUser($actor)->authorize('publish-decisions');

            $paper = Paper::query()->where('public_id', $publicId)->lockForUpdate()->firstOrFail();
            $decision = PaperDecision::query()->where('paper_id', $paper->id)->lockForUpdate()->firstOrFail();

            if ($decision->version !== $expectedVersion || $paper->status->value !== $decision->outcome) {
                throw ValidationException::withMessages(['correction' => 'The decision changed. Refresh before correcting it.']);
            }

            $review = $decision->sourceReview()->firstOrFail();
            // Corrections retain the same automatic author-facing General Comments policy.
            $comments = trim($review->general_comments ?? '');
            $approvedComments = $comments === '' ? null : $comments;

            if ($decision->outcome === $newOutcome) {
                throw ValidationException::withMessages(['correction' => 'Choose a different published outcome to issue a correction.']);
            }

            $oldOutcome = $decision->outcome;
            $now = now();
            $decision->outcome = $newOutcome;
            $decision->public_comments = $approvedComments;
            $decision->version++;
            $decision->setAttribute('published_by', $actor->id);
            $decision->setAttribute('published_at', $now);
            $decision->save();

            $event = $decision->events()->make();
            $event->paper_id = $paper->id;
            $event->actor_id = $actor->id;
            $event->version = $decision->version;
            $event->event_type = 'corrected';
            $event->from_outcome = $oldOutcome;
            $event->to_outcome = $newOutcome;
            $event->reviewer_recommendation = $review->recommendation->value;
            $event->public_comments = $approvedComments;
            $event->internal_reason = $reason;
            $event->setAttribute('occurred_at', $now);
            $event->save();

            $paper->status = PaperStatus::from($newOutcome);
            $paper->save();

            $owner = $paper->owner()->firstOrFail();
            $outbox = new DecisionNotification;
            $outbox->setAttribute('paper_id', $paper->id);
            $outbox->paper_decision_event_id = $event->id;
            $outbox->decision_version = $decision->version;
            $outbox->event_type = 'corrected';
            $outbox->recipient_email = $owner->email;
            $outbox->recipient_name = $owner->name;
            $outbox->paper_reference = $paper->reference;
            $outbox->paper_title = $paper->title;
            $outbox->outcome = $newOutcome;
            $outbox->public_comments = $approvedComments;
            $outbox->status = 'queued';
            $outbox->save();

            SendDecisionNotification::dispatch($outbox->id)->afterCommit();

            return $decision;
        }, 3);
    }
}
