<?php

namespace App\Jobs;

use App\Mail\AcademicDecisionMail;
use App\Models\DecisionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendDecisionNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly string $notificationId) {}

    public function handle(): void
    {
        $notice = DB::transaction(function (): ?DecisionNotification {
            $item = DecisionNotification::query()->whereKey($this->notificationId)->lockForUpdate()->first();

            if ($item === null || $item->status !== 'queued') {
                return null;
            }

            $item->status = 'processing';
            $item->attempts++;
            $item->setAttribute('last_attempt_at', now());
            $item->last_error = null;
            $item->save();

            return $item;
        });

        if ($notice === null) {
            return;
        }

        try {
            Mail::to($notice->recipient_email)->send(new AcademicDecisionMail($notice->load('paper')));

            DecisionNotification::query()->whereKey($notice->id)
                ->where('status', 'processing')
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'failed_at' => null,
                    'last_error' => null,
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            report($exception);

            DecisionNotification::query()->whereKey($notice->id)
                ->where('status', 'processing')
                ->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'last_error' => mb_substr($exception->getMessage(), 0, 1000),
                    'updated_at' => now(),
                ]);
        }
    }
}
