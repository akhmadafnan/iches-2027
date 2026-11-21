<?php

namespace App\Console\Commands;

use App\Jobs\SendDecisionNotification;
use App\Models\DecisionNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class RecoverDecisionNotifications extends Command
{
    protected $signature = 'decisions:recover-notifications {--failed : Include previously failed notifications for explicit retry}';

    protected $description = 'Requeue unsent academic decision outbox entries after a queue interruption';

    public function handle(): int
    {
        $recovered = 0;

        DecisionNotification::query()
            ->where(function ($query): void {
                $query->where('status', 'queued')
                    ->orWhere(function ($stale): void {
                        $stale->where('status', 'processing')
                            ->where('last_attempt_at', '<', now()->subMinutes(10));
                    });
                if ($this->option('failed')) {
                    $query->orWhere('status', 'failed');
                }
            })
            ->orderBy('created_at')
            ->chunkById(100, function ($notifications) use (&$recovered): void {
                foreach ($notifications as $row) {
                    DB::transaction(function () use ($row, &$recovered): void {
                        $notice = DecisionNotification::query()->whereKey($row->id)->lockForUpdate()->first();
                        if ($notice === null || $notice->status === 'sent') {
                            return;
                        }

                        if ($notice->status === 'processing' && ($notice->last_attempt_at === null ||
                            $notice->last_attempt_at->greaterThan(now()->subMinutes(10)))) {
                            return;
                        }
                        if ($notice->status === 'failed' && ! $this->option('failed')) {
                            return;
                        }

                        $notice->status = 'queued';
                        $notice->save();

                        SendDecisionNotification::dispatch($notice->id)->afterCommit();
                        $recovered++;
                    });
                }
            });

        $this->components->info("Requeued {$recovered} notification jobs. Queue workers must be running for delivery.");

        return self::SUCCESS;
    }
}
