<?php

namespace App\Actions\Academic;

use App\Enums\PaperReviewStatus;
use App\Enums\PaperStatus;
use App\Jobs\SendDecisionNotification;
use App\Models\DecisionNotification;
use App\Models\Paper;
use App\Models\PaperDecision;
use App\Models\PaperReviewClaim;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

final class PublishAcademicDecisions
{
    /**
     * @param  list<string>  $publicIds
     * @param  array<string, array{outcome: string, reason: string}>  $overrides
     * @return array{published: int, skipped: int, failed: int, notifications: int}
     */
    public function handle(User $actor, array $publicIds, array $overrides = []): array
    {
        Gate::forUser($actor)->authorize('publish-decisions');

        if (count($publicIds) > 1000) {
            throw ValidationException::withMessages(['publish' => 'Publish up to 1,000 papers in one confirmed batch.']);
        }

        $result = ['published' => 0, 'skipped' => 0, 'failed' => 0, 'notifications' => 0];

        foreach (array_unique($publicIds) as $publicId) {
            if (strlen($publicId) !== 26) {
                $result['skipped']++;

                continue;
            }

            try {
                $notificationId = $this->publishOne(
                    $actor,
                    $publicId,
                    $overrides[$publicId] ?? null,
                );

                if ($notificationId === null) {
                    $result['skipped']++;
                } else {
                    $result['published']++;
                    $result['notifications']++;
                }
            } catch (ValidationException $exception) {
                // An invalid override is a rejected item; never silently publish reviewer default.
                $result['failed']++;
            } catch (Throwable $exception) {
                report($exception);
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * @param  array{outcome: string, reason: string}|null  $override
     */
    private function publishOne(User $actor, string $publicId, ?array $override): ?string
    {
        return DB::transaction(function () use ($actor, $publicId, $override): ?string {
            Gate::forUser($actor)->authorize('publish-decisions');

            $paper = Paper::query()->where('public_id', $publicId)->lockForUpdate()->first();

            if ($paper === null || $paper->status !== PaperStatus::Submitted ||
                PaperDecision::query()->where('paper_id', $paper->id)->exists()) {
                return null;
            }

            $claim = PaperReviewClaim::query()->where('active_paper_id', $paper->id)
                ->with('review')->lockForUpdate()->first();
            $review = $claim?->review;

            if ($review === null || $review->status !== PaperReviewStatus::Submitted ||
                $review->recommendation === null) {
                return null;
            }

            $recommended = $review->recommendation->value === 'accept' ? 'accepted' : 'rejected';
            $outcome = $recommended;
            $reason = null;

            if ($override !== null) {
                $target = $override['outcome'];
                $reason = trim($override['reason']);
                if (! in_array($target, ['accepted', 'rejected'], true) ||
                    $target === $recommended || mb_strlen($reason) < 10 || mb_strlen($reason) > 2000) {
                    throw ValidationException::withMessages(['publish' => 'Invalid override: choose a different outcome and provide at least 10 characters of internal justification.']);
                }

                $outcome = $target;
            }

            $now = now();
            // General Comments are author-facing by policy, automatically released only upon publication.
            $comments = trim($review->general_comments ?? '');
            $approvedComments = $comments === '' ? null : $comments;

            $decision = new PaperDecision;
            $decision->setAttribute('paper_id', $paper->id);
            $decision->source_review_id = $review->id;
            $decision->outcome = $outcome;
            $decision->public_comments = $approvedComments;
            $decision->version = 1;
            $decision->setAttribute('published_by', $actor->id);
            $decision->setAttribute('published_at', $now);
            $decision->save();

            $event = $decision->events()->make();
            $event->paper_id = $paper->id;
            $event->actor_id = $actor->id;
            $event->version = 1;
            $event->event_type = 'published';
            $event->from_outcome = null;
            $event->to_outcome = $outcome;
            $event->reviewer_recommendation = $review->recommendation->value;
            $event->public_comments = $approvedComments;
            $event->internal_reason = $reason;
            $event->setAttribute('occurred_at', $now);
            $event->save();

            $paper->status = PaperStatus::from($outcome);
            $paper->save();

            $owner = $paper->owner()->firstOrFail();
            $outbox = new DecisionNotification;
            $outbox->setAttribute('paper_id', $paper->id);
            $outbox->paper_decision_event_id = $event->id;
            $outbox->decision_version = 1;
            $outbox->event_type = 'published';
            $outbox->recipient_email = $owner->email;
            $outbox->recipient_name = $owner->name;
            $outbox->paper_reference = $paper->reference;
            $outbox->paper_title = $paper->title;
            $outbox->outcome = $outcome;
            $outbox->public_comments = $approvedComments;
            $outbox->status = 'queued';
            $outbox->save();

            SendDecisionNotification::dispatch($outbox->id)->afterCommit();

            return $outbox->id;
        }, 3);
    }
}
