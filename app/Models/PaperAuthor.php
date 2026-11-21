<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $paper_id
 * @property int $position
 * @property string $name
 * @property string $email
 * @property string|null $institution
 * @property string|null $country
 * @property bool $is_corresponding
 */
class PaperAuthor extends Model
{
    protected $fillable = [
        'position',
        'name',
        'email',
        'institution',
        'country',
        'is_corresponding',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_corresponding' => 'boolean',
        ];
    }

    /** @return BelongsTo<Paper, $this> */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }
}
