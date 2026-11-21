<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $paper_id
 * @property int $actor_id
 * @property string $event
 * @property string $from_status
 * @property string $to_status
 * @property int $submission_version
 * @property Carbon $occurred_at
 */
class PaperStatusHistory extends Model
{
    protected $fillable = [
        'actor_id',
        'event',
        'from_status',
        'to_status',
        'submission_version',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'submission_version' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Paper, $this> */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }
}
