<?php

namespace Tests\Feature\Admin;

use App\Enums\ProgramCode;
use App\Enums\UserRole;
use App\Livewire\Admin\ManageEventSettings;
use App\Livewire\Admin\ManagePrograms;
use App\Models\EventMilestone;
use App\Models\EventSetting;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class EventMasterManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EventMasterSeeder::class);
    }

    public function test_tor_seeds_exactly_one_event_five_programs_and_seven_dates(): void
    {
        $this->assertDatabaseCount('event_settings', 1);
        $this->assertDatabaseCount('programs', 5);
        $this->assertDatabaseCount('event_milestones', 7);
        $this->assertEqualsCanonicalizing(
            array_map(fn (ProgramCode $code) => $code->value, ProgramCode::cases()),
            Program::query()->get()->map(fn (Program $p) => $p->code->value)->all(),
        );
        $event = EventSetting::query()->findOrFail(1);
        $this->assertSame('2027-01-05', $event->starts_on->format('Y-m-d'));
        $this->assertSame('2027-01-06', $event->ends_on->format('Y-m-d'));
        $this->assertSame('Asia/Jakarta', $event->timezone);
        $this->assertNull($event->start_time);
        $this->assertStringContainsString('ISLAMIC VALUES IN A CHANGING WORLD', $event->theme);
        $this->assertSame('hybrid', Program::query()->where('code', 'conference')->firstOrFail()->delivery_mode);
        $this->assertNull(Program::query()->where('code', 'research_pkm')->firstOrFail()->delivery_mode);
        $this->assertSame('2026-12-01', EventMilestone::query()->where('code', 'abstract_submission')->firstOrFail()->starts_on->format('Y-m-d'));
        $this->assertSame('2027-01-28', EventMilestone::query()->where('code', 'proceedings')->firstOrFail()->ends_on->format('Y-m-d'));
    }

    public function test_seed_is_idempotent_and_preserves_admin_edits(): void
    {
        $program = Program::query()->firstOrFail();
        $date = EventMilestone::query()->firstOrFail();
        $program->update(['name' => 'Edited program']);
        $date->update(['name' => 'Edited milestone']);
        EventSetting::query()->findOrFail(1)->update(['main_venue' => 'New venue']);
        $this->seed(EventMasterSeeder::class);
        $this->seed(EventMasterSeeder::class);
        $this->assertDatabaseCount('event_settings', 1);
        $this->assertDatabaseCount('programs', 5);
        $this->assertDatabaseCount('event_milestones', 7);
        $this->assertSame('Edited program', $program->fresh()->name);
        $this->assertSame('Edited milestone', $date->fresh()->name);
        $this->assertSame('New venue', EventSetting::query()->findOrFail(1)->main_venue);
    }

    public function test_admin_reads_both_flux_pages_and_pagination(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        $this->get(route('admin.programs'))->assertOk()->assertSee('International Conference')->assertSee('Page 1 of 1')->assertSee('Previous')->assertSee('Next');
        $this->get(route('admin.event-settings'))->assertOk()->assertSee('Important dates')->assertSee('Abstract Submission')->assertSee('Start time pending')->assertSee('Page 1 of 1');
    }

    public function test_every_non_admin_is_forbidden_by_route_and_livewire(): void
    {
        foreach ([UserRole::User, UserRole::Reviewer, UserRole::Committee] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('admin.programs'))->assertForbidden();
            $this->get(route('admin.event-settings'))->assertForbidden();
            $this->assertFalse(Gate::forUser($user)->allows('manage-programs'));
            $this->assertFalse(Gate::forUser($user)->allows('manage-event-settings'));
            Livewire::actingAs($user)->test(ManagePrograms::class)->assertForbidden();
            Livewire::actingAs($user)->test(ManageEventSettings::class)->assertForbidden();
        }
    }

    public function test_new_account_profile_requirement_still_blocks_both_admin_pages(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'profile_setup_required' => true]);
        $this->actingAs($admin)->get(route('admin.programs'))->assertRedirect(route('profile.edit'));
        $this->get(route('admin.event-settings'))->assertRedirect(route('profile.edit'));
    }

    public function test_admin_edits_a_program_but_cannot_change_its_machine_code(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $program = Program::query()->where('code', 'conference')->firstOrFail();
        Livewire::actingAs($admin)->test(ManagePrograms::class)
            ->call('edit', $program->id)->assertSet('showEditModal', true)
            ->set('editingCode', 'falsified')
            ->set('name', 'ICHES Conference Updated')
            ->set('venue', 'Hall to be confirmed')
            ->set('sortOrder', 3)
            ->call('save')->assertHasNoErrors()->assertSet('showEditModal', false);
        $this->assertSame(ProgramCode::Conference, $program->fresh()->code);
        $this->assertSame('ICHES Conference Updated', $program->fresh()->name);
        $this->assertSame(3, $program->fresh()->sort_order);
        $this->assertDatabaseCount('programs', 5);
    }

    public function test_invalid_program_dates_and_mode_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $program = Program::query()->firstOrFail();
        Livewire::actingAs($admin)->test(ManagePrograms::class)
            ->call('edit', $program->id)
            ->set('startsOn', '2027-01-06')->set('endsOn', '2027-01-05')
            ->set('deliveryMode', 'fake')->set('name', 'A')
            ->call('save')->assertHasErrors(['endsOn', 'deliveryMode', 'name']);
        $this->assertSame($program->name, $program->fresh()->name);
    }

    public function test_program_search_does_not_show_unrelated_results(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Livewire::actingAs($admin)->test(ManagePrograms::class)
            ->set('search', 'research_pkm')->assertSee('Pembinaan Riset dan PKM')
            ->assertDontSee('International Community Service')
            ->set('search', 'not-a-program')->assertSee('No programs found');
    }

    public function test_event_settings_are_editable_with_optional_start_hour(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Livewire::actingAs($admin)->test(ManageEventSettings::class)
            ->assertSet('startTime', '')->assertSee('Start time pending')
            ->set('mainVenue', 'Updated Hall')->call('save')->assertHasNoErrors();
        $this->assertSame('Updated Hall', EventSetting::query()->findOrFail(1)->main_venue);
        $this->assertNull(EventSetting::query()->findOrFail(1)->start_time);
        Livewire::actingAs($admin)->test(ManageEventSettings::class)
            ->set('startTime', '08:15')->call('save')->assertHasNoErrors();
        $this->assertSame('08:15', substr(EventSetting::query()->findOrFail(1)->start_time, 0, 5));
    }

    public function test_invalid_event_date_order_and_timezone_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Livewire::actingAs($admin)->test(ManageEventSettings::class)
            ->set('startsOn', '2027-01-07')->set('endsOn', '2027-01-05')
            ->set('timezone', 'Not/A_Real_Zone')
            ->call('save')->assertHasErrors(['endsOn', 'timezone']);
        $this->assertSame('2027-01-05', EventSetting::query()->findOrFail(1)->starts_on->format('Y-m-d'));
    }

    public function test_milestone_edit_cancel_validate_and_save(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $milestone = EventMilestone::query()->where('code', 'abstract_submission')->firstOrFail();
        Livewire::actingAs($admin)->test(ManageEventSettings::class)
            ->call('editMilestone', $milestone->id)
            ->set('milestoneName', 'Cancelled title')->call('cancelMilestone')
            ->assertSet('showMilestoneModal', false);
        $this->assertSame('Abstract Submission', $milestone->fresh()->name);

        Livewire::actingAs($admin)->test(ManageEventSettings::class)
            ->call('editMilestone', $milestone->id)
            ->set('milestoneStartsOn', '2027-01-10')
            ->set('milestoneEndsOn', '2027-01-05')
            ->call('saveMilestone')->assertHasErrors('milestoneEndsOn');
        Livewire::actingAs($admin)->test(ManageEventSettings::class)
            ->call('editMilestone', $milestone->id)
            ->set('milestoneName', 'Updated Abstract Dates')
            ->set('milestoneStartsOn', '2026-12-03')
            ->set('milestoneEndsOn', '2026-12-22')
            ->set('milestoneNotes', 'Officially approved')
            ->call('saveMilestone')->assertHasNoErrors()->assertSet('showMilestoneModal', false);
        $milestone->refresh();
        $this->assertSame('abstract_submission', $milestone->code);
        $this->assertSame('Updated Abstract Dates', $milestone->name);
        $this->assertSame('2026-12-03', $milestone->starts_on->format('Y-m-d'));
    }
}
