<?php

namespace Tests\Feature\Access;

use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_all_private_role_areas(): void
    {
        foreach (['admin.users', 'reviewer.workspace', 'committee.workspace'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_four_core_roles_have_distinct_server_side_access(): void
    {
        $routes = [
            'admin.users' => UserRole::Admin,
            'reviewer.workspace' => UserRole::Reviewer,
            'committee.workspace' => UserRole::Committee,
        ];

        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);

            foreach ($routes as $route => $allowedRole) {
                $response = $this->actingAs($user)->get(route($route));

                if ($role === $allowedRole) {
                    $response->assertOk();
                } else {
                    $response->assertForbidden();
                }
            }
        }
    }

    public function test_admin_cannot_change_own_role_but_can_manage_other_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $other = User::factory()->create();

        $this->assertFalse(Gate::forUser($admin)->allows('updateAccess', $admin));
        $this->assertTrue(Gate::forUser($admin)->allows('updateAccess', $other));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', User::class));

        $this->assertFalse(Gate::forUser($other)->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser($other)->allows('updateAccess', $admin));
    }

    public function test_sensitive_authority_requires_separate_explicit_staff_assignment(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $committee = User::factory()->create(['role' => UserRole::Committee]);

        $this->assertFalse(Gate::forUser($admin)->allows('verify-payments'));
        $this->assertFalse(Gate::forUser($admin)->allows('publish-decisions'));
        $this->assertFalse(Gate::forUser($committee)->allows('verify-payments'));

        StaffAssignment::query()->create([
            'user_id' => $committee->id,
            'duty' => StaffDuty::Finance,
            'scope_type' => 'global',
            'scope_id' => 0,
        ]);

        $this->assertTrue(Gate::forUser($committee)->allows('verify-payments'));
        $this->assertFalse(Gate::forUser($committee)->allows('publish-decisions'));
        $this->assertFalse(Gate::forUser($admin)->allows('verify-payments'));
    }

    public function test_moderator_and_cohost_pic_assignments_are_scoped_to_exact_resource(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('moderate-session', [12]));
        $this->assertFalse(Gate::forUser($user)->allows('manage-cohost', [5]));

        StaffAssignment::query()->create([
            'user_id' => $user->id,
            'duty' => StaffDuty::Moderator,
            'scope_type' => 'session',
            'scope_id' => 12,
        ]);

        StaffAssignment::query()->create([
            'user_id' => $user->id,
            'duty' => StaffDuty::CoHostPic,
            'scope_type' => 'institution',
            'scope_id' => 5,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('moderate-session', [12]));
        $this->assertFalse(Gate::forUser($user)->allows('moderate-session', [13]));
        $this->assertTrue(Gate::forUser($user)->allows('manage-cohost', [5]));
        $this->assertFalse(Gate::forUser($user)->allows('manage-cohost', [6]));
        $this->assertFalse($user->hasGlobalDuty(StaffDuty::Moderator));
    }
}
