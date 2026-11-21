<?php

namespace App\Actions\Access;

use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class UpdateUserAccess
{
    /**
     * @param  list<string>  $assignedRoles  Empty means replace with primary role (legacy caller).
     */
    public function execute(
        User $actor,
        User $target,
        UserRole $role,
        bool $finance,
        bool $academicAuthority,
        array $assignedRoles = [],
    ): void {
        DB::transaction(function () use ($actor, $target, $role, $finance, $academicAuthority, $assignedRoles): void {
            $target = User::query()->lockForUpdate()->findOrFail($target->id);
            Gate::forUser($actor)->authorize('updateAccess', $target);

            $values = $assignedRoles === [] ? [$role->value] : array_values(array_unique($assignedRoles));
            if (count($values) > count(UserRole::cases())
                || ! in_array($role->value, $values, true)) {
                throw ValidationException::withMessages(['roles' => 'Assign at least one role, including the selected primary role.']);
            }
            foreach ($values as $value) {
                if (UserRole::tryFrom($value) === null) {
                    throw ValidationException::withMessages(['roles' => 'An invalid role was submitted.']);
                }
            }

            $wantsRoot = in_array(UserRole::Superadmin->value, $values, true);
            $hasRoot = $target->hasRole(UserRole::Superadmin);
            if (($wantsRoot || $hasRoot) && ! $actor->isActiveAs(UserRole::Superadmin)) {
                throw ValidationException::withMessages(['roles' => 'Only an active Superadmin may grant, edit or revoke Superadmin.']);
            }

            // A root must not accidentally delete the last root membership.
            if ($hasRoot && ! $wantsRoot
                && DB::table('user_roles')->where('role', UserRole::Superadmin->value)->count() <= 1) {
                throw ValidationException::withMessages(['roles' => 'The last Superadmin cannot be revoked.']);
            }

            if (($finance || $academicAuthority)
                && array_intersect($values, [UserRole::Committee->value, UserRole::Admin->value, UserRole::Superadmin->value]) === []) {
                throw ValidationException::withMessages([
                    'role' => 'Finance and Academic Authority require Committee, Admin or Superadmin membership.',
                ]);
            }

            $before = $target->assignedRoles();
            $target->roleAssignments()->whereNotIn('role', $values)->delete();
            foreach ($values as $value) {
                $target->roleAssignments()->firstOrCreate(['role' => $value]);
            }

            // Preserve legacy primary column for registrations and old migrations.
            $target->role = $role;
            $target->save();

            foreach ([
                StaffDuty::Finance->value => $finance,
                StaffDuty::AcademicAuthority->value => $academicAuthority,
            ] as $duty => $enabled) {
                $query = $target->staffAssignments()->where('duty', $duty)
                    ->where('scope_type', 'global')->where('scope_id', 0);
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

            Log::info('ICHES user access changed', [
                'actor_id' => $actor->id,
                'target_id' => $target->id,
                'old_roles' => array_map(fn (UserRole $r): string => $r->value, $before),
                'new_roles' => $values,
                'primary_role' => $role->value,
                'finance' => $finance,
                'academic_authority' => $academicAuthority,
            ]);
        });
    }
}
