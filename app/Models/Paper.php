<?php

namespace App\Models;

use App\Enums\PaperStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property int $conference_track_id
 * @property string $public_id
 * @property string|null $reference
 * @property string $title
 * @property PaperStatus $status
 * @property string|null $abstract_text
 * @property list<string>|null $keywords
 * @property string|null $abstract_pdf_path
 * @property string|null $abstract_pdf_original_name
 * @property int|null $abstract_pdf_size
 * @property Carbon|null $abstract_pdf_uploaded_at
 * @property Carbon|null $submitted_at
 * @property int $submission_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Paper extends Model
{
    protected $fillable = [
        'title',
        'conference_track_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaperStatus::class,
            'keywords' => 'array',
            'abstract_pdf_size' => 'integer',
            'abstract_pdf_uploaded_at' => 'datetime',
            'submitted_at' => 'datetime',
            'submission_version' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<ConferenceTrack, $this> */
    public function conferenceTrack(): BelongsTo
    {
        return $this->belongsTo(ConferenceTrack::class);
    }

    /** @return HasOne<PaperDecision, $this> */
    public function decision(): HasOne
    {
        return $this->hasOne(PaperDecision::class);
    }

    /** @return HasMany<DecisionNotification, $this> */
    public function decisionNotifications(): HasMany
    {
        return $this->hasMany(DecisionNotification::class)->orderByDesc('decision_version');
    }

    /** @return HasMany<PaperReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(PaperReview::class);
    }

    /** @return HasMany<PaperReviewClaim, $this> */
    public function reviewClaims(): HasMany
    {
        return $this->hasMany(PaperReviewClaim::class);
    }

    /** @return HasMany<PaperStatusHistory, $this> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(PaperStatusHistory::class)->orderBy('occurred_at')->orderBy('id');
    }

    /** @return HasMany<PaperAuthor, $this> */
    public function authors(): HasMany
    {
        return $this->hasMany(PaperAuthor::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @param  Builder<Paper>  $query
     * @return Builder<Paper>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('owner_id', $user->id);
    }
}
