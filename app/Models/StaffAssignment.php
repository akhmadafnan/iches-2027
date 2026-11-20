<?php

namespace App\Models;

use App\Enums\StaffDuty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property StaffDuty $duty
 * @property string $scope_type
 * @property int $scope_id
 * @property-read User $user
 */
class StaffAssignment extends Model
{
    protected $fillable = ['user_id', 'duty', 'scope_type', 'scope_id'];

    protected function casts(): array
    {
        return [
            'duty' => StaffDuty::class,
            'scope_id' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
