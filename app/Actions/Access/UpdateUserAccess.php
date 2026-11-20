<?php

namespace App\Actions\Access;

use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateUserAccess
{
    public function execute(
        User $actor,
        User $target,
        UserRole $role,
        bool $finance,
        bool $academicAuthority,
    ): void {
        DB::transaction(function () use ($actor, $target, $role, $finance, $academicAuthority): void {
            $target = User::query()->lockForUpdate()->findOrFail($target->id);

            Gate::forUser($actor)->authorize('updateAccess', $target);

            if (($finance || $academicAuthority) && ! in_array($role, [UserRole::Committee, UserRole::Admin], true)) {
                throw ValidationException::withMessages([
                    'role' => 'Finance and Academic Authority assignments require Committee or Admin role.',
                ]);
            }

            $target->role = $role;
            $target->save();

            $assignments = [
                StaffDuty::Finance->value => $finance,
                StaffDuty::AcademicAuthority->value => $academicAuthority,
            ];

            foreach ($assignments as $duty => $enabled) {
                $query = $target->staffAssignments()
                    ->where('duty', $duty)
                    ->where('scope_type', 'global')
                    ->where('scope_id', 0);

                if ($enabled) {
                    $target->staffAssignments()->firstOrCreate([
                        'duty' => $duty,
                        'scope_type' => 'global',
                        'scope_id' => 0,
                    ]);
                } else {
                    $query->delete();
                }
            }
        });
    }
}
