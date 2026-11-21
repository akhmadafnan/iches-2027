<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $paper_review_id
 * @property int $actor_id
 * @property string $event
 * @property int $version
 * @property Carbon $occurred_at
 */
class PaperReviewEvent extends Model
{
    use HasUuids;

    protected $fillable = ['paper_review_id', 'actor_id', 'event', 'version', 'occurred_at'];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PaperReview, $this> */
    public function review(): BelongsTo
    {
        return $this->belongsTo(PaperReview::class, 'paper_review_id');
    }
}
