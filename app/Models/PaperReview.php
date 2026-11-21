<?php

namespace App\Models;

use App\Enums\PaperReviewRecommendation;
use App\Enums\PaperReviewStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $paper_review_claim_id
 * @property int $paper_id
 * @property int $reviewer_id
 * @property string|null $general_comments
 * @property PaperReviewRecommendation|null $recommendation
 * @property PaperReviewStatus $status
 * @property int $lock_version
 * @property Carbon|null $submitted_at
 */
class PaperReview extends Model
{
    use HasUuids;

    protected $fillable = [
        'paper_review_claim_id',
        'paper_id',
        'reviewer_id',
        'general_comments',
        'recommendation',
        'status',
        'lock_version',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'recommendation' => PaperReviewRecommendation::class,
            'status' => PaperReviewStatus::class,
            'lock_version' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<PaperReviewClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(PaperReviewClaim::class, 'paper_review_claim_id');
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

    /** @return HasMany<PaperReviewEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PaperReviewEvent::class)->orderBy('version');
    }
}
