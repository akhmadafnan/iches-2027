<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $paper_id
 * @property int $reviewer_id
 * @property int|null $active_paper_id
 * @property Carbon $claimed_at
 * @property Carbon|null $released_at
 * @property int|null $released_by
 * @property string|null $release_reason
 */
class PaperReviewClaim extends Model
{
    use HasUuids;

    protected $fillable = [
        'paper_id',
        'reviewer_id',
        'active_paper_id',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'released_at' => 'datetime',
            'active_paper_id' => 'integer',
            'paper_id' => 'integer',
            'reviewer_id' => 'integer',
        ];
    }

    /** @return HasOne<PaperReview, $this> */
    public function review(): HasOne
    {
        return $this->hasOne(PaperReview::class, 'paper_review_claim_id');
    }

    /** @return BelongsTo<Paper, $this> */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
