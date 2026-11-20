<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property string|null $theme
 * @property string|null $organizer
 * @property string|null $main_venue
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property string|null $start_time
 * @property string $timezone
 */
class EventSetting extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'theme',
        'organizer',
        'main_venue',
        'starts_on',
        'ends_on',
        'start_time',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }
}
