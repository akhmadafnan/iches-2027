<?php

namespace App\Actions\Academic;

use App\Jobs\SendDecisionNotification;
use App\Models\DecisionNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class RetryDecisionNotification
{
    public function handle(User $actor, string $notificationId): void
    {
        Gate::forUser($actor)->authorize('publish-decisions');

        DB::transaction(function () use ($actor, $notificationId): void {
            Gate::forUser($actor)->authorize('publish-decisions');

            $notice = DecisionNotification::query()->whereKey($notificationId)->lockForUpdate()->firstOrFail();
            if (! in_array($notice->status, ['failed', 'queued'], true)) {
                throw ValidationException::withMessages(['notification' => 'Only failed or queued notifications can be requeued.']);
            }

            $notice->status = 'queued';
            $notice->last_error = null;
            $notice->save();

            SendDecisionNotification::dispatch($notice->id)->afterCommit();
        });
    }
}
