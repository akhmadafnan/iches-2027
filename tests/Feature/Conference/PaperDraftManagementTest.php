<?php

namespace Tests\Feature\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Enums\PaperStatus;
use App\Enums\UserRole;
use App\Livewire\Conference\PaperEditor;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\PaperAuthor;
use App\Models\User;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class PaperDraftManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
    }

    private function track(): ConferenceTrack
    {
        return ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail();
    }

    private function createDraft(User $owner, string $title = 'Interdisciplinary Values in Education'): Paper
    {
        return app(SavePaperDraft::class)->handle(
            $owner,
            null,
            ['title' => $title, 'conference_track_id' => $this->track()->id],
            [['name' => $owner->name, 'email' => $owner->email, 'institution' => $owner->institution, 'country' => $owner->country]],
            0,
        );
    }

    public function test_guest_is_redirected_and_incomplete_profile_must_finish_onboarding(): void
    {
        $this->get(route('papers.index'))->assertRedirect(route('login'));
        $this->get(route('papers.create'))->assertRedirect(route('login'));

        $unready = User::factory()->create(['profile_setup_required' => true]);
        $this->actingAs($unready)->get(route('papers.index'))->assertRedirect(route('profile.edit'));
        $this->get(route('papers.create'))->assertRedirect(route('profile.edit'));
    }

    public function test_new_draft_form_is_accessible_with_eight_tor_tracks_and_owner_defaults(): void
    {
        $owner = User::factory()->create(['name' => 'Afnan Academic', 'email' => 'afnan.demo@example.test', 'institution' => 'UNISYA', 'country' => 'Indonesia']);

        $this->actingAs($owner)->get(route('papers.create'))
            ->assertOk()
            ->assertSee('New Paper Draft')
            ->assertSee('Islamic Studies, Ethics, and Contemporary Civilization')
            ->assertSee('Environmental Sustainability, and Human Well-Being')
            ->assertSee('Save draft');

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->assertSet('authors.0.name', 'Afnan Academic')
            ->assertSet('authors.0.email', 'afnan.demo@example.test')
            ->assertSet('authors.0.institution', 'UNISYA')
            ->assertSet('authors.0.country', 'Indonesia')
            ->assertSet('correspondingIndex', 0);
    }

    public function test_saving_new_draft_creates_immutable_reference_public_id_and_one_author(): void
    {
        $owner = User::factory()->create();

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->set('title', 'Ethical Innovations for Inclusive Education')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('papers.index'));

        $paper = Paper::query()->sole();
        $this->assertSame($owner->id, $paper->owner_id);
        $this->assertSame($this->track()->id, $paper->conference_track_id);
        $this->assertSame('ICH27-P000001', $paper->reference);
        $this->assertSame(26, strlen($paper->public_id));
        $this->assertSame(PaperStatus::Draft, $paper->status);
        $this->assertDatabaseCount('paper_authors', 1);
        $this->assertSame($owner->email, $paper->authors->first()->email);
        $this->assertTrue($paper->authors->first()->is_corresponding);

        $this->actingAs($owner)->get(route('papers.index'))
            ->assertOk()
            ->assertSee($paper->reference)
            ->assertSee($paper->title)
            ->assertSee('Page 1 of 1')
            ->assertSee('Previous')
            ->assertSee('Next');
    }

    public function test_authors_are_ordered_and_exactly_one_is_corresponding(): void
    {
        $owner = User::factory()->create();
        $track = $this->track();

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->set('title', 'Community and Society Research Paper')
            ->set('conferenceTrackId', (string) $track->id)
            ->call('addAuthor')
            ->set('authors.1.name', 'Colleague One')
            ->set('authors.1.email', 'COLLEAGUE@EXAMPLE.COM')
            ->set('authors.1.institution', 'Partner University')
            ->set('authors.1.country', 'Malaysia')
            ->set('correspondingIndex', 1)
            ->call('save')
            ->assertHasNoErrors();

        $paper = Paper::query()->sole();
        $authors = $paper->authors;

        $this->assertCount(2, $authors);
        $this->assertSame([1, 2], $authors->pluck('position')->all());
        $this->assertSame('colleague@example.com', $authors->last()->email);
        $this->assertSame('Partner University', $authors->last()->institution);
        $this->assertSame('Malaysia', $authors->last()->country);
        $this->assertFalse($authors->first()->is_corresponding);
        $this->assertTrue($authors->last()->is_corresponding);
        $this->assertSame(1, PaperAuthor::query()->where('is_corresponding', true)->count());
    }

    public function test_author_reordering_and_removal_keep_corresponding_selection_consistent(): void
    {
        $owner = User::factory()->create();

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->call('addAuthor')
            ->set('authors.1.name', 'Research Partner')
            ->set('authors.1.email', 'research@example.com')
            ->set('correspondingIndex', 1)
            ->call('moveAuthor', 1, 'up')
            ->assertSet('authors.0.name', 'Research Partner')
            ->assertSet('correspondingIndex', 0)
            ->call('moveAuthor', 0, 'down')
            ->assertSet('authors.1.name', 'Research Partner')
            ->assertSet('correspondingIndex', 1)
            ->call('removeAuthor', 0)
            ->assertSet('authors.0.name', 'Research Partner')
            ->assertSet('correspondingIndex', 0)
            ->call('removeAuthor', 0)
            ->assertCount('authors', 1);
    }

    public function test_editing_own_draft_preserves_permanent_ids_and_replaces_author_metadata(): void
    {
        $owner = User::factory()->create();
        $paper = $this->createDraft($owner);
        $originalId = $paper->id;
        $originalReference = $paper->reference;
        $originalPublicId = $paper->public_id;

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->assertSet('editingPublicId', $originalPublicId)
            ->assertSet('paperReference', $originalReference)
            ->set('title', 'Ethical Education and Social Resilience (Revised)')
            ->call('addAuthor')
            ->set('authors.1.name', 'Additional Contributor')
            ->set('authors.1.email', 'contributor@example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('papers.index'));

        $paper->refresh();

        $this->assertSame($originalId, $paper->id);
        $this->assertSame($originalReference, $paper->reference);
        $this->assertSame($originalPublicId, $paper->public_id);
        $this->assertSame(PaperStatus::Draft, $paper->status);
        $this->assertSame(2, $paper->authors()->count());
        $this->assertSame('Ethical Education and Social Resilience (Revised)', $paper->title);
    }

    public function test_two_drafts_have_different_permanent_identifiers(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $a = $this->createDraft($first);
        $b = $this->createDraft($second);

        $this->assertNotSame($a->id, $b->id);
        $this->assertNotSame($a->public_id, $b->public_id);
        $this->assertNotSame($a->reference, $b->reference);
        $this->assertSame('ICH27-P000002', $b->reference);
        $this->assertDatabaseCount('papers', 2);
    }

    public function test_owner_scoping_blocks_other_accounts_from_viewing_and_editing(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $paper = $this->createDraft($owner);

        $this->actingAs($other)->get(route('papers.index'))
            ->assertOk()
            ->assertDontSee($paper->reference)
            ->assertDontSee($paper->title);

        $this->get(route('papers.edit', $paper->public_id))->assertNotFound();

        $this->assertFalse(Gate::forUser($other)->allows('view', $paper));
        $this->assertFalse(Gate::forUser($other)->allows('update', $paper));

        Livewire::actingAs($other)->test(PaperEditor::class, ['paper' => $paper])->assertNotFound();

        $this->assertSame('Interdisciplinary Values in Education', $paper->fresh()->title);
    }

    public function test_cross_account_save_action_is_forbidden_and_does_not_touch_paper(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $paper = $this->createDraft($owner);
        $original = $paper->title;

        try {
            app(SavePaperDraft::class)->handle(
                $other,
                $paper,
                ['title' => 'Malicious title', 'conference_track_id' => $this->track()->id],
                [['name' => $other->name, 'email' => $other->email]],
                0,
            );
            $this->fail('An unauthorized actor should not be able to update another paper.');
        } catch (AuthorizationException $exception) {
            $this->assertSame($original, $paper->fresh()->title);
        }
    }

    public function test_paper_with_non_draft_status_cannot_be_edited(): void
    {
        $owner = User::factory()->create();
        $paper = $this->createDraft($owner);
        $paper->status = PaperStatus::Submitted;
        $paper->save();

        $this->assertFalse(Gate::forUser($owner)->allows('update', $paper));
        $this->actingAs($owner)->get(route('papers.edit', $paper->public_id))->assertForbidden();
        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])->assertForbidden();
        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
    }

    public function test_invalid_fields_or_duplicate_author_emails_do_not_save_partial_paper(): void
    {
        $owner = User::factory()->create();
        $track = $this->track();

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->set('title', 'ABC')
            ->set('conferenceTrackId', (string) $track->id)
            ->call('addAuthor')
            ->set('authors.1.name', 'Second Researcher')
            ->set('authors.1.email', strtoupper($owner->email))
            ->call('save')
            ->assertHasErrors(['title', 'authors.1.email']);

        $this->assertDatabaseCount('papers', 0);
        $this->assertDatabaseCount('paper_authors', 0);
    }

    public function test_invalid_corresponding_author_index_is_rejected(): void
    {
        $owner = User::factory()->create();

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->set('title', 'Academic Paper for Testing Correspondence')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->set('correspondingIndex', 99)
            ->call('save')
            ->assertHasErrors('correspondingIndex');

        $this->assertDatabaseCount('papers', 0);
    }

    public function test_disabled_track_cannot_be_selected_for_new_draft_but_existing_draft_can_keep_it(): void
    {
        $owner = User::factory()->create();
        $paper = $this->createDraft($owner);
        $track = $this->track();
        $track->update(['is_active' => false]);

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->set('title', 'New Draft with Disabled Track')
            ->set('conferenceTrackId', (string) $track->id)
            ->call('save')
            ->assertHasErrors('conferenceTrackId');

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('title', 'Updated Older Draft on Archived Track')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('papers', 1);
        $this->assertSame('Updated Older Draft on Archived Track', $paper->fresh()->title);
    }

    public function test_any_authenticated_role_can_own_a_draft_without_gaining_extra_privileges(): void
    {
        foreach ([UserRole::User, UserRole::Reviewer, UserRole::Committee, UserRole::Admin] as $role) {
            $actor = User::factory()->create(['role' => $role]);
            $this->actingAs($actor)->get(route('papers.create'))->assertOk();
            $this->assertTrue(Gate::forUser($actor)->allows('create', Paper::class));
        }
    }
}
