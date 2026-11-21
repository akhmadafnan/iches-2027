<?php

namespace Tests\Feature\Access;

use App\Actions\Access\UpdateUserAccess;
use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MultiRoleSuperadminTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_new_account_has_one_persisted_membership_matching_primary_role(): void
    {
        $reviewer = User::factory()->create(['role' => UserRole::Reviewer]);
        $user = User::factory()->create();

        $this->assertEquals([UserRole::Reviewer], $reviewer->assignedRoles());
        $this->assertEquals([UserRole::User], $user->assignedRoles());
        $this->assertDatabaseCount('user_roles', 2);
    }

    public function test_one_account_can_have_many_roles_but_only_active_role_gates_apply(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $user->roleAssignments()->create(['role' => UserRole::Reviewer]);

        $this->actingAs($user)->get(route('admin.users'))->assertOk();
        $this->actingAs($user)->get(route('reviewer.workspace'))->assertForbidden();

        $this->actingAs($user)->post(route('workspace.switch-role'), ['role' => 'reviewer'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(UserRole::Reviewer, $user->activeRole());
        $this->get(route('reviewer.workspace'))->assertOk();
        $this->get(route('admin.users'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()->assertSee('Reviewer');

        $this->post(route('workspace.switch-role'), ['role' => 'admin'])
            ->assertRedirect(route('dashboard'));
        $this->assertSame(UserRole::Admin, $user->activeRole());
        $this->get(route('admin.users'))->assertOk();
    }

    public function test_switch_to_unassigned_or_unrecognized_role_is_denied_without_changing_context(): void
    {
        $user = User::factory()->create(['role' => UserRole::Reviewer]);
        $this->actingAs($user)
            ->post(route('workspace.switch-role'), ['role' => UserRole::Superadmin->value])
            ->assertForbidden();

        $this->assertSame(UserRole::Reviewer, $user->activeRole());
        $this->post(route('workspace.switch-role'), ['role' => 'root'])->assertSessionHasErrors('role');
        $this->get(route('admin.users'))->assertForbidden();
    }

    public function test_deleted_active_role_immediately_falls_back_to_authorized_primary_role(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $user->roleAssignments()->create(['role' => UserRole::Reviewer]);
        $this->actingAs($user)->post(route('workspace.switch-role'), ['role' => 'reviewer']);
        $this->assertSame(UserRole::Reviewer, $user->activeRole());

        $user->roleAssignments()->where('role', UserRole::Reviewer->value)->delete();

        $this->assertSame(UserRole::Admin, $user->activeRole());
        $this->get(route('reviewer.workspace'))->assertForbidden();
        $this->get(route('admin.users'))->assertOk();
    }

    public function test_session_role_cannot_spill_into_another_authenticated_account(): void
    {
        $one = User::factory()->create(['role' => UserRole::Admin]);
        $one->roleAssignments()->create(['role' => UserRole::Reviewer]);
        $other = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($one)->post(route('workspace.switch-role'), ['role' => 'reviewer']);
        $this->actingAs($other)->get(route('admin.users'))->assertForbidden();
        $this->assertSame(UserRole::User, $other->activeRole());
    }

    public function test_superadmin_active_context_accesses_all_existing_module_gates_without_special_staff_duties(): void
    {
        $root = User::factory()->create(['role' => UserRole::Superadmin]);

        $this->assertTrue(Gate::forUser($root)->allows('manage-users'));
        $this->assertTrue(Gate::forUser($root)->allows('review-papers'));
        $this->assertTrue(Gate::forUser($root)->allows('scan-services'));
        $this->assertTrue(Gate::forUser($root)->allows('publish-decisions'));
        $this->assertTrue(Gate::forUser($root)->allows('verify-payments'));
        $this->assertTrue(Gate::forUser($root)->allows('moderate-session', [101]));
        $this->assertTrue(Gate::forUser($root)->allows('manage-cohost', [222]));
        $this->assertFalse($root->hasGlobalDuty(StaffDuty::Finance));

        $this->actingAs($root)->get(route('academic.decisions'))->assertOk();
        $this->get(route('reviewer.workspace'))->assertOk();
        $this->get(route('admin.users'))->assertOk();
    }

    public function test_superadmin_does_not_impersonate_a_paper_owner(): void
    {
        $root = User::factory()->create(['role' => UserRole::Superadmin]);
        $owner = User::factory()->create();
        $paper = new Paper;
        $paper->owner_id = $owner->id;

        $this->assertFalse(Gate::forUser($root)->allows('view', $paper));
    }

    public function test_an_active_superadmin_who_switches_to_reviewer_loses_root_permissions(): void
    {
        $root = User::factory()->create(['role' => UserRole::Superadmin]);
        $root->roleAssignments()->create(['role' => UserRole::Reviewer]);

        $this->actingAs($root)->post(route('workspace.switch-role'), ['role' => 'reviewer'])
            ->assertRedirect(route('dashboard'));
        $this->assertFalse(Gate::forUser($root)->allows('manage-users'));
        $this->assertFalse(Gate::forUser($root)->allows('publish-decisions'));
        $this->assertTrue(Gate::forUser($root)->allows('review-papers'));

        $this->post(route('workspace.switch-role'), ['role' => 'superadmin'])
            ->assertRedirect(route('dashboard'));
        $this->assertTrue(Gate::forUser($root)->allows('publish-decisions'));
    }

    public function test_admin_may_assign_multiple_normal_roles_but_not_superadmin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create();
        app(UpdateUserAccess::class)->execute(
            $admin, $target, UserRole::Reviewer, false, false, ['reviewer', 'committee']
        );

        $this->assertEqualsCanonicalizing([UserRole::Reviewer, UserRole::Committee], $target->fresh()->assignedRoles());
        $this->assertSame(UserRole::Reviewer, $target->fresh()->role);

        $this->expectException(ValidationException::class);
        app(UpdateUserAccess::class)->execute(
            $admin, $target, UserRole::Superadmin, false, false, ['superadmin', 'reviewer']
        );
    }

    public function test_superadmin_can_delegate_root_but_cannot_edit_own_membership(): void
    {
        $root = User::factory()->create(['role' => UserRole::Superadmin]);
        $target = User::factory()->create();

        app(UpdateUserAccess::class)->execute(
            $root, $target, UserRole::Superadmin, false, false, ['superadmin', 'user']
        );
        $this->assertTrue($target->fresh()->hasRole(UserRole::Superadmin));
        $this->assertTrue($target->fresh()->hasRole(UserRole::User));
        $this->assertFalse(Gate::forUser($root)->allows('updateAccess', $root));
    }

    public function test_demo_account_with_known_default_password_can_never_bootstrap_superadmin(): void
    {
        $demo = User::factory()->create(['email' => 'admin@iches.id']);
        $this->artisan('access:bootstrap-superadmin', [
            'email' => $demo->email,
            '--confirm' => true,
        ])->assertExitCode(1);
        $this->assertFalse($demo->fresh()->hasRole(UserRole::Superadmin));

        $other = User::factory()->create(['email' => 'non-demo@example.test']);
        $this->artisan('access:bootstrap-superadmin', [
            'email' => $other->email,
            '--confirm' => true,
        ])->assertExitCode(1);
        $this->assertFalse($other->fresh()->hasRole(UserRole::Superadmin));
    }

    public function test_local_bootstrap_requires_explicit_confirm_and_never_creates_a_user_or_password(): void
    {
        $existing = User::factory()->create(['password' => 'SecureLocalOwnerPassword!2026']);

        $this->artisan('access:bootstrap-superadmin', ['email' => $existing->email])
            ->assertExitCode(1);
        $this->assertFalse($existing->fresh()->hasRole(UserRole::Superadmin));

        $this->artisan('access:bootstrap-superadmin', [
            'email' => $existing->email,
            '--confirm' => true,
        ])->assertExitCode(0);

        $this->assertTrue($existing->fresh()->hasRole(UserRole::Superadmin));
        $this->assertCount(5, $existing->fresh()->assignedRoles());
        $this->assertSame(UserRole::Superadmin, $existing->fresh()->role);
        $this->assertDatabaseCount('users', 1);

        $second = User::factory()->create();
        $this->artisan('access:bootstrap-superadmin', [
            'email' => $second->email,
            '--confirm' => true,
        ])->assertExitCode(1);
        $this->assertFalse($second->fresh()->hasRole(UserRole::Superadmin));
    }
}
