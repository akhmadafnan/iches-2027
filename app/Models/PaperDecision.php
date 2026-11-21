<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $paper_id
 * @property string $source_review_id
 * @property string $outcome
 * @property string|null $public_comments
 * @property int $version
 * @property int $published_by
 * @property Carbon $published_at
 * @property-read Paper $paper
 * @property-read PaperReview $sourceReview
 * @property-read Collection<int, PaperDecisionEvent> $events
 */
class PaperDecision extends Model
{
    use HasUuids;

    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Paper, $this> */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    /** @return BelongsTo<PaperReview, $this> */
    public function sourceReview(): BelongsTo
    {
        return $this->belongsTo(PaperReview::class, 'source_review_id');
    }

    /** @return HasMany<PaperDecisionEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PaperDecisionEvent::class)->orderBy('version');
    }
}
