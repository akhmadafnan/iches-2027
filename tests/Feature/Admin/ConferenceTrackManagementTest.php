<?php

namespace Tests\Feature\Admin;

use App\Enums\ProgramCode;
use App\Enums\UserRole;
use App\Livewire\Admin\ManageConferenceTracks;
use App\Models\ConferenceTrack;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ConferenceTrackManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
    }

    public function test_all_eight_official_tor_tracks_are_seeded_in_order(): void
    {
        $titles = [
            'Islamic Studies, Ethics, and Contemporary Civilization',
            'Education, Pedagogy, and Human Development',
            'Leadership, Governance, and Institutional Accountability',
            'Economics, Sustainable Business, and Inclusive Development',
            'Law, Human Rights, Social Justice, and Public Policy',
            'Social Sciences, Humanities, Culture, and Community Resilience',
            'Digital Transformation, Responsible Innovation, and Society',
            'Environmental Sustainability, and Human Well-Being',
        ];

        $conference = Program::query()->where('code', ProgramCode::Conference->value)->firstOrFail();
        $tracks = $conference->conferenceTracks()->available()->get();

        $this->assertDatabaseCount('conference_tracks', 8);
        $this->assertCount(8, $tracks);
        $this->assertSame($titles, $tracks->pluck('official_title')->all());
        $this->assertSame($titles, $tracks->pluck('name')->all());
        $this->assertSame(range(1, 8), $tracks->pluck('sort_order')->all());

        foreach ($tracks as $index => $track) {
            $this->assertSame(sprintf('TRK-%02d', $index + 1), $track->code);
            $this->assertTrue($track->is_active);
            $this->assertNull($track->description);
            $this->assertSame($conference->id, $track->program_id);
        }
    }

    public function test_reseeding_does_not_duplicate_or_overwrite_edited_track(): void
    {
        $track = ConferenceTrack::query()->where('code', 'TRK-03')->firstOrFail();
        $id = $track->id;

        $track->update([
            'name' => 'Admin-modified leadership title',
            'description' => 'Reviewed by academic coordinator',
            'is_active' => false,
            'sort_order' => 18,
        ]);

        $this->seed(ConferenceTrackSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);

        $this->assertDatabaseCount('conference_tracks', 8);
        $this->assertSame($id, $track->fresh()->id);
        $this->assertSame('Admin-modified leadership title', $track->fresh()->name);
        $this->assertSame('Reviewed by academic coordinator', $track->fresh()->description);
        $this->assertFalse($track->fresh()->is_active);
        $this->assertSame(18, $track->fresh()->sort_order);
        $this->assertSame('Leadership, Governance, and Institutional Accountability', $track->fresh()->official_title);
    }

    public function test_available_scope_excludes_inactive_tracks_and_orders_choices(): void
    {
        ConferenceTrack::query()->where('code', 'TRK-02')->firstOrFail()->update(['is_active' => false]);

        $conference = Program::query()->where('code', ProgramCode::Conference->value)->firstOrFail();
        $options = $conference->conferenceTracks()->available()->get(['id', 'name', 'code']);

        $this->assertCount(7, $options);
        $this->assertFalse($options->pluck('code')->contains('TRK-02'));
        $this->assertSame('TRK-01', $options->first()->code);
        $this->assertSame('TRK-08', $options->last()->code);
    }

    public function test_admin_can_access_the_flux_ui_table_and_open_modal(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.tracks'))
            ->assertOk()
            ->assertSee('Conference Tracks')
            ->assertSee('Islamic Studies, Ethics, and Contemporary Civilization')
            ->assertSee('Environmental Sustainability, and Human Well-Being')
            ->assertSee('Page 1 of 1')
            ->assertSee('Previous')
            ->assertSee('Next')
            ->assertSee('No.');

        $target = ConferenceTrack::query()->where('code', 'TRK-04')->firstOrFail();

        Livewire::actingAs($admin)->test(ManageConferenceTracks::class)
            ->call('edit', $target->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editingCode', 'TRK-04')
            ->assertSet('officialTitle', $target->official_title)
            ->call('cancel')
            ->assertSet('showEditModal', false);
    }

    public function test_admin_can_edit_display_data_without_mutating_immutable_identifiers(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $track = ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail();
        $sourceTitle = $track->official_title;

        Livewire::actingAs($admin)->test(ManageConferenceTracks::class)
            ->call('edit', $track->id)
            ->set('editingCode', 'TRK-99')
            ->set('officialTitle', 'Forged official TOR title')
            ->set('name', 'Revised Islamic Studies Track')
            ->set('description', 'Updated description for submitters')
            ->set('sortOrder', 11)
            ->set('isActive', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $track->refresh();

        $this->assertSame('TRK-01', $track->code);
        $this->assertSame($sourceTitle, $track->official_title);
        $this->assertSame('Revised Islamic Studies Track', $track->name);
        $this->assertSame(11, $track->sort_order);
        $this->assertFalse($track->is_active);
        $this->assertDatabaseCount('conference_tracks', 8);
    }

    public function test_invalid_or_duplicate_names_and_invalid_order_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $track = ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail();
        $another = ConferenceTrack::query()->where('code', 'TRK-02')->firstOrFail();

        Livewire::actingAs($admin)->test(ManageConferenceTracks::class)
            ->call('edit', $track->id)
            ->set('name', $another->name)
            ->set('sortOrder', 100)
            ->call('save')
            ->assertHasErrors(['name', 'sortOrder']);

        $this->assertSame($track->name, $track->fresh()->name);
    }

    public function test_admin_cannot_disable_the_final_available_track(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $lastTrack = ConferenceTrack::query()->where('code', 'TRK-08')->firstOrFail();

        ConferenceTrack::query()->whereKeyNot($lastTrack->id)->update(['is_active' => false]);

        Livewire::actingAs($admin)->test(ManageConferenceTracks::class)
            ->call('edit', $lastTrack->id)
            ->set('isActive', false)
            ->call('save')
            ->assertHasErrors('isActive');

        $this->assertTrue($lastTrack->fresh()->is_active);
        $this->assertSame(1, ConferenceTrack::query()->available()->count());
    }

    public function test_search_and_status_filters_work_together(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        ConferenceTrack::query()->where('code', 'TRK-05')->firstOrFail()->update(['is_active' => false]);

        Livewire::actingAs($admin)->test(ManageConferenceTracks::class)
            ->set('search', 'TRK-05')
            ->assertSee('Law, Human Rights, Social Justice, and Public Policy')
            ->set('statusFilter', 'active')
            ->assertSee('No matching tracks')
            ->set('statusFilter', 'inactive')
            ->assertSee('Law, Human Rights, Social Justice, and Public Policy')
            ->set('search', '')
            ->assertSee('Page 1 of 1');
    }

    public function test_guest_and_non_admin_roles_cannot_read_or_mutate_tracks(): void
    {
        $this->get(route('admin.tracks'))->assertRedirect(route('login'));

        foreach ([UserRole::User, UserRole::Reviewer, UserRole::Committee] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('admin.tracks'))->assertForbidden();
            $this->assertFalse(Gate::forUser($user)->allows('manage-conference-tracks'));
            Livewire::actingAs($user)->test(ManageConferenceTracks::class)->assertForbidden();
        }
    }

    public function test_profile_setup_guard_still_protects_academic_admin_route(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'profile_setup_required' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.tracks'))->assertRedirect(route('profile.edit'));
    }
}
