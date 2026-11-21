<?php

namespace Tests\Feature\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Livewire\Conference\PaperEditor;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorModalExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
        Cache::flush();
    }

    private function track(): ConferenceTrack
    {
        return ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail();
    }

    private function rorStub(): void
    {
        $org = [
            'id' => 'https://ror.org/041rf3s62',
            'status' => 'active',
            'names' => [
                ['value' => 'Universitas Islam Syarifuddin Lumajang', 'types' => ['ror_display', 'label']],
                ['value' => 'UNISYA', 'types' => ['acronym']],
            ],
            'locations' => [
                ['geonames_details' => ['name' => 'Lumajang', 'country_name' => 'Indonesia']],
            ],
        ];

        Http::preventStrayRequests();
        Http::fake([
            'api.ror.org/v2/organizations?*' => Http::response(['number_of_results' => 1, 'items' => [$org]], 200),
            'api.ror.org/v2/organizations/041rf3s62' => Http::response($org, 200),
        ]);
    }

    public function test_create_and_edit_buttons_open_modal_without_exposing_contact_fields_in_the_list(): void
    {
        $user = User::factory()->create(['name' => 'Example User', 'institution' => 'UNISYA']);

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->assertSee('Authors &amp; affiliations', escape: false)
            ->assertSee('Edit author 1')
            ->assertDontSee('Example User email address')
            ->call('editAuthor', 0)
            ->assertSet('authorModalOpen', true)
            ->assertSet('editingAuthorIndex', 0)
            ->assertSet('authorDraft.name', 'Example User')
            ->assertSet('authorDraft.institution_query', 'UNISYA')
            ->call('cancelAuthor')
            ->assertSet('authorModalOpen', false)
            ->call('addAuthor')
            ->assertSet('authorModalOpen', true)
            ->assertSet('editingAuthorIndex', null)
            ->assertCount('authors', 1);
    }

    public function test_cancel_add_does_not_leave_empty_contributor(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->call('addAuthor')
            ->set('authorDraft.name', 'Temporary Person')
            ->set('authorDraft.email', 'temp@example.org')
            ->call('cancelAuthor')
            ->assertSet('authorModalOpen', false)
            ->assertCount('authors', 1)
            ->assertSet('authors.0.name', $user->name);

        $this->assertDatabaseCount('paper_authors', 0);
    }

    public function test_cancel_edit_discards_changes_and_preserves_existing_author(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->call('editAuthor', 0)
            ->set('authorDraft.name', 'Changed Name')
            ->set('authorDraft.email', 'changed@example.org')
            ->call('cancelAuthor')
            ->assertSet('authors.0.name', 'Original Name')
            ->assertSet('authors.0.email', $user->email)
            ->call('editAuthor', 0)
            ->assertSet('authorDraft.name', 'Original Name');
    }

    public function test_modal_rejects_duplicate_email_and_does_not_commit_second_author(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->call('addAuthor')
            ->set('authorDraft.name', 'Second Contributor')
            ->set('authorDraft.email', strtoupper($user->email))
            ->call('useManualAffiliation')
            ->set('authorDraft.institution', 'Partner University')
            ->call('saveAuthor')
            ->assertHasErrors('authorDraft.email')
            ->assertSet('authorModalOpen', true)
            ->assertCount('authors', 1);
    }

    public function test_modal_can_assign_corresponding_author_and_reorder_stably(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->call('addAuthor')
            ->set('authorDraft.name', 'Research Collaborator')
            ->set('authorDraft.email', 'collaborator@example.org')
            ->call('useManualAffiliation')
            ->set('authorDraft.institution', 'Research Partner Institute')
            ->set('authorDraftCorresponding', true)
            ->call('saveAuthor')
            ->assertHasNoErrors()
            ->assertCount('authors', 2)
            ->assertSet('correspondingIndex', 1)
            ->call('moveAuthor', 1, 'up')
            ->assertSet('authors.0.name', 'Research Collaborator')
            ->assertSet('correspondingIndex', 0)
            ->call('setCorrespondingAuthor', 1)
            ->assertSet('correspondingIndex', 1)
            ->call('removeAuthor', 1)
            ->assertSet('correspondingIndex', 0)
            ->assertCount('authors', 1);
    }

    public function test_modal_ror_lookup_saves_canonical_affiliation_and_keeps_country_independent(): void
    {
        $this->rorStub();
        $user = User::factory()->create(['institution' => 'UNISYA', 'country' => 'Malaysia']);

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->set('title', 'Education and Community in Southeast Asia')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->set('authorDraft.institution_query', 'UNISYA')
            ->assertSee('Universitas Islam Syarifuddin Lumajang')
            ->call('selectInstitution', 'https://ror.org/041rf3s62')
            ->assertSet('authorDraft.affiliation_source', 'ror')
            ->call('saveAuthor')
            ->assertSet('authors.0.institution', 'Universitas Islam Syarifuddin Lumajang')
            ->assertSet('authors.0.ror_id', 'https://ror.org/041rf3s62')
            ->call('save')
            ->assertHasNoErrors();

        $author = Paper::query()->sole()->authors()->sole();
        $this->assertSame('Universitas Islam Syarifuddin Lumajang', $author->institution);
        $this->assertSame('https://ror.org/041rf3s62', $author->ror_id);
        $this->assertSame('Malaysia', $author->country);
    }

    public function test_existing_paper_identity_stays_fixed_after_modal_edit(): void
    {
        $user = User::factory()->create();
        $draft = app(SavePaperDraft::class)->handle(
            $user,
            null,
            ['title' => 'Social History of Indonesian Education', 'conference_track_id' => $this->track()->id],
            [['name' => $user->name, 'email' => $user->email, 'institution' => 'Existing Research Institution', 'affiliation_source' => 'manual']],
            0,
        );

        $reference = $draft->reference;
        $ulid = $draft->public_id;

        Livewire::actingAs($user)->test(PaperEditor::class, ['paper' => $draft])
            ->call('editAuthor', 0)
            ->set('authorDraft.institution', 'Revised Research Institution')
            ->call('saveAuthor')
            ->assertSet('authors.0.institution', 'Revised Research Institution')
            ->call('save')
            ->assertHasNoErrors();

        $draft->refresh();
        $this->assertSame($reference, $draft->reference);
        $this->assertSame($ulid, $draft->public_id);
        $this->assertSame('Revised Research Institution', $draft->authors()->sole()->institution);
    }
}
