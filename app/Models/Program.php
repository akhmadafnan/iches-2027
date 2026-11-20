<?php

namespace App\Models;

use App\Enums\ProgramCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ProgramCode $code
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $starts_on
 * @property Carbon|null $ends_on
 * @property string|null $venue
 * @property string|null $delivery_mode
 * @property int $sort_order
 */
class Program extends Model
{
    protected $fillable = [
        'name',
        'description',
        'starts_on',
        'ends_on',
        'venue',
        'delivery_mode',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'code' => ProgramCode::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'sort_order' => 'integer',
        ];
    }
}
