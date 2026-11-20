<?php

namespace Tests\Feature;

use App\Enums\RegistrationIntention;
use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Livewire\Dashboard;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_shows_intention_specific_guidance_for_submit_paper(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'registration_intention' => RegistrationIntention::SubmitPaper,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Participant Portal')
            ->assertSee('Submit a Paper')
            ->assertSee('Prepare your abstract submission')
            ->assertSee('Coming soon')
            ->assertSee('Review my profile')
            ->assertDontSee('Users & Access')
            ->assertDontSee('Reviewer Workspace')
            ->assertDontSee('Committee Workspace');
    }

    public function test_user_dashboard_guidance_for_conference_attendance(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'registration_intention' => RegistrationIntention::JoinConference,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Join as Conference Participant')
            ->assertSee('Prepare for conference participation')
            ->assertSee('PROG-001')
            ->assertDontSee('Prepare your abstract submission');
    }

    public function test_user_dashboard_guidance_for_explore_programs(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'registration_intention' => RegistrationIntention::ExplorePrograms,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Explore ICHES Programs')
            ->assertSee('Explore the ICHES programs')
            ->assertSee('FND-006')
            ->assertDontSee('Open users & access');
    }

    public function test_admin_dashboard_displays_only_actual_account_counts_and_admin_navigation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create(['role' => UserRole::Reviewer]);
        User::factory()->unverified()->create(['role' => UserRole::Committee]);
        User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->assertViewHas('summary', fn (array $summary): bool => $summary === [
                'accounts' => 4,
                'verified' => 3,
                'staff' => 3,
            ])
            ->assertSee('Registered accounts')
            ->assertSee('Verified emails')
            ->assertSee('Staff accounts')
            ->assertSee('Manage users & access')
            ->assertDontSee('Your registration intention');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Administration')
            ->assertSee(route('admin.users'), false)
            ->assertDontSee('Reviewer Workspace')
            ->assertDontSee('Committee Workspace');
    }

    public function test_reviewer_dashboard_only_links_to_reviewer_workspace(): void
    {
        $reviewer = User::factory()->create(['role' => UserRole::Reviewer]);

        $this->actingAs($reviewer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Academic Review')
            ->assertSee('Reviewer Workspace')
            ->assertSee(route('reviewer.workspace'), false)
            ->assertSee('REV-001')
            ->assertDontSee('Users & Access')
            ->assertDontSee('Committee Workspace')
            ->assertDontSee('Registered accounts');
    }

    public function test_committee_dashboard_only_links_to_committee_workspace(): void
    {
        $committee = User::factory()->create(['role' => UserRole::Committee]);

        $this->actingAs($committee)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Committee Operations')
            ->assertSee('Committee Workspace')
            ->assertSee(route('committee.workspace'), false)
            ->assertSee('QRS modules')
            ->assertDontSee('Users & Access')
            ->assertDontSee('Reviewer Workspace')
            ->assertDontSee('Registered accounts');
    }

    public function test_explicit_staff_duties_are_displayed_only_to_the_assigned_actor(): void
    {
        $committee = User::factory()->create(['role' => UserRole::Committee]);
        $anotherCommittee = User::factory()->create(['role' => UserRole::Committee]);

        StaffAssignment::query()->create([
            'user_id' => $committee->id,
            'duty' => StaffDuty::Finance,
            'scope_type' => 'global',
            'scope_id' => 0,
        ]);

        $this->actingAs($committee)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Your delegated duties')
            ->assertSee('Finance verification')
            ->assertDontSee('Academic decision authority');

        $this->actingAs($anotherCommittee)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Your delegated duties')
            ->assertDontSee('Finance verification');
    }

    public function test_new_account_still_must_finish_profile_before_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'profile_setup_required' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_security_and_profile_links_are_present_but_starter_kit_links_are_gone(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Security')
            ->assertDontSee('Repository')
            ->assertDontSee('Documentation');
    }
}
