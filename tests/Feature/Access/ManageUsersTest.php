<?php

namespace Tests\Feature\Access;

use App\Actions\Access\UpdateUserAccess;
use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Livewire\Admin\ManageUsers;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ManageUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_access_opens_and_closes_the_flux_modal(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['name' => 'ICHES Modal UAT']);

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->assertSet('showEditModal', false)
            ->call('edit', $target->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editingName', 'ICHES Modal UAT')
            ->assertSet('editingEmail', $target->email)
            ->call('cancel')
            ->assertSet('showEditModal', false)
            ->assertSet('editingUserId', null);

        $this->assertSame(UserRole::User, $target->fresh()->role);
    }

    public function test_modal_closes_after_successful_access_change(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->call('edit', $target->id)
            ->assertSet('showEditModal', true)
            ->set('role', UserRole::Reviewer->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $this->assertSame(UserRole::Reviewer, $target->fresh()->role);
    }

    public function test_directory_shows_row_numbers_and_exact_pagination_footer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        User::factory()->count(22)->create();

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->assertSee('No.')
            ->assertSee('Page 1 of 3')
            ->assertSee('Previous')
            ->assertSee('Next')
            ->call('nextPage')
            ->assertSee('Page 2 of 3')
            ->call('nextPage')
            ->assertSee('Page 3 of 3')
            ->call('previousPage')
            ->assertSee('Page 2 of 3');
    }

    public function test_search_filters_users_and_resets_pagination(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->count(14)->create();
        User::factory()->create(['name' => 'Distinctive ICHES Participant']);

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->call('nextPage')
            ->assertSee('Page 2 of 2')
            ->set('search', 'Distinctive ICHES Participant')
            ->assertSee('Distinctive ICHES Participant')
            ->assertSee('Page 1 of 1')
            ->assertDontSee('Page 2 of 2')
            ->set('search', 'This user does not exist')
            ->assertSee('No users found')
            ->assertSee('Page 1 of 1');
    }

    public function test_admin_can_load_user_management_livewire_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create(['name' => 'ICHES Delegate']);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Users &amp; Access', false)
            ->assertSee('ICHES Delegate');
    }

    public function test_admin_can_change_a_users_role_using_livewire(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->call('edit', $target->id)
            ->assertSet('editingUserId', $target->id)
            ->set('role', UserRole::Reviewer->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(UserRole::Reviewer, $target->fresh()->role);
        $this->assertFalse(Gate::forUser($target->fresh())->allows('manage-users'));
        $this->assertTrue(Gate::forUser($target->fresh())->allows('review-papers'));
    }

    public function test_admin_can_grant_and_revoke_explicit_finance_and_academic_duties(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::Committee]);

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->call('edit', $target->id)
            ->set('finance', true)
            ->set('academicAuthority', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($target->hasGlobalDuty(StaffDuty::Finance));
        $this->assertTrue($target->hasGlobalDuty(StaffDuty::AcademicAuthority));

        Livewire::test(ManageUsers::class)
            ->call('edit', $target->id)
            ->set('role', UserRole::User->value)
            ->set('finance', false)
            ->set('academicAuthority', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(UserRole::User, $target->fresh()->role);
        $this->assertFalse($target->hasGlobalDuty(StaffDuty::Finance));
        $this->assertFalse($target->hasGlobalDuty(StaffDuty::AcademicAuthority));
    }

    public function test_finance_privilege_cannot_be_granted_to_unprivileged_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->call('edit', $target->id)
            ->set('finance', true)
            ->call('save')
            ->assertHasErrors('role');

        $this->assertSame(UserRole::User, $target->fresh()->role);
        $this->assertDatabaseCount('staff_assignments', 0);
    }

    public function test_admin_cannot_edit_own_role_or_privileges_even_through_forged_livewire_state(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->set('editingUserId', $admin->id)
            ->set('role', UserRole::User->value)
            ->set('finance', true)
            ->call('save')
            ->assertForbidden();

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
        $this->assertDatabaseCount('staff_assignments', 0);
    }

    public function test_non_admin_cannot_invoke_user_management_component_actions(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ManageUsers::class)
            ->assertForbidden();
    }

    public function test_action_rejects_unauthorized_role_changes_even_when_called_directly(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(UpdateUserAccess::class)->execute(
            $user,
            $target,
            UserRole::Admin,
            false,
            false,
        );
    }
}
