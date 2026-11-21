<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $paper_id
 * @property string $paper_decision_event_id
 * @property int $decision_version
 * @property string $event_type
 * @property string $recipient_email
 * @property string $recipient_name
 * @property string $paper_reference
 * @property string $paper_title
 * @property string $outcome
 * @property string|null $public_comments
 * @property string $status
 * @property int $attempts
 * @property Carbon|null $last_attempt_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $failed_at
 * @property string|null $last_error
 * @property-read Paper $paper
 */
class DecisionNotification extends Model
{
    use HasUuids;

    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'decision_version' => 'integer',
            'attempts' => 'integer',
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Paper, $this> */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    /** @return BelongsTo<PaperDecisionEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(PaperDecisionEvent::class, 'paper_decision_event_id');
    }
}
