<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $paper_decision_id
 * @property int $paper_id
 * @property int $actor_id
 * @property int $version
 * @property string $event_type
 * @property string|null $from_outcome
 * @property string $to_outcome
 * @property string $reviewer_recommendation
 * @property string|null $public_comments
 * @property string|null $internal_reason
 * @property Carbon $occurred_at
 */
class PaperDecisionEvent extends Model
{
    use HasUuids;

    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PaperDecision, $this> */
    public function decision(): BelongsTo
    {
        return $this->belongsTo(PaperDecision::class, 'paper_decision_id');
    }

    /** @return BelongsTo<Paper, $this> */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }
}
