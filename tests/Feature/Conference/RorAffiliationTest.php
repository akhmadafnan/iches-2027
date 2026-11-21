<?php

namespace Tests\Feature\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Livewire\Conference\PaperEditor;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use App\Services\RorOrganizationService;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class RorAffiliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
        Cache::flush();
    }

    /**
     * @return array<string, mixed>
     */
    private function unisya(string $status = 'active'): array
    {
        return [
            'id' => 'https://ror.org/041rf3s62',
            'status' => $status,
            'names' => [
                ['value' => 'UNISYA', 'types' => ['acronym'], 'lang' => 'id'],
                ['value' => 'Universitas Islam Syarifuddin Lumajang', 'types' => ['label', 'ror_display'], 'lang' => 'id'],
            ],
            'locations' => [
                ['geonames_details' => ['name' => 'Lumajang', 'country_name' => 'Indonesia']],
            ],
        ];
    }

    private function fakeRor(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'api.ror.org/v2/organizations?*' => Http::response(['number_of_results' => 1, 'items' => [$this->unisya()]], 200),
            'api.ror.org/v2/organizations/041rf3s62' => Http::response($this->unisya(), 200),
        ]);
    }

    private function track(): ConferenceTrack
    {
        return ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail();
    }

    public function test_search_returns_official_name_alias_location_and_ror_id(): void
    {
        $this->fakeRor();

        $results = app(RorOrganizationService::class)->search('UNISYA');

        $this->assertCount(1, $results);
        $this->assertSame('https://ror.org/041rf3s62', $results[0]['id']);
        $this->assertSame('Universitas Islam Syarifuddin Lumajang', $results[0]['name']);
        $this->assertSame('UNISYA', $results[0]['aliases']);
        $this->assertSame('Lumajang, Indonesia', $results[0]['location']);
        $this->assertSame([], app(RorOrganizationService::class)->search('U'));
    }

    public function test_selected_organization_is_verified_by_id_and_not_just_display_text(): void
    {
        $this->fakeRor();
        $actor = User::factory()->create(['institution' => 'UNISYA']);

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->set('title', 'Community-Based Education and Responsibility')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->set('authorDraft.institution_query', 'UNISYA')
            ->assertSee('Universitas Islam Syarifuddin Lumajang')
            ->assertSee('Lumajang, Indonesia')
            ->call('selectInstitution', 'https://ror.org/041rf3s62')
            ->assertSet('authorDraft.affiliation_source', 'ror')
            ->assertSet('authorDraft.ror_id', 'https://ror.org/041rf3s62')
            ->assertDontSee('Institution not listed in ROR')
            ->set('authorDraft.institution', 'Forged institution name')
            ->call('saveAuthor')
            ->assertSet('authors.0.institution', 'Universitas Islam Syarifuddin Lumajang')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('papers.index'));

        $author = Paper::query()->sole()->authors()->sole();
        $this->assertSame('Universitas Islam Syarifuddin Lumajang', $author->institution);
        $this->assertSame('https://ror.org/041rf3s62', $author->ror_id);
        $this->assertSame('ror', $author->affiliation_source);
        $this->assertSame('Indonesia', $author->country);
    }

    public function test_typing_without_selecting_does_not_count_as_verified_affiliation(): void
    {
        $this->fakeRor();
        $actor = User::factory()->create(['institution' => 'UNISYA']);

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->set('title', 'Digital Inclusion and Human Development')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->set('authorDraft.institution_query', 'UNISYA')
            ->call('saveAuthor')
            ->assertHasErrors('authorDraft.affiliation_source');

        $this->assertDatabaseCount('papers', 0);
    }

    public function test_changing_the_search_text_clears_a_previously_verified_id(): void
    {
        $this->fakeRor();
        $actor = User::factory()->create(['institution' => 'UNISYA']);

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->call('editAuthor', 0)
            ->set('authorDraft.institution_query', 'UNISYA')
            ->call('selectInstitution', 'https://ror.org/041rf3s62')
            ->assertSet('authorDraft.affiliation_source', 'ror')
            ->set('authorDraft.institution_query', 'U')
            ->assertSet('authorDraft.ror_id', '')
            ->assertSet('authorDraft.affiliation_source', '')
            ->assertSet('authorDraft.institution', '');
    }

    public function test_explicit_manual_fallback_has_no_fabricated_ror_identifier(): void
    {
        $actor = User::factory()->create(['institution' => 'Local Community Institute']);

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->set('title', 'Inclusive Community Service and Research')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->call('useManualAffiliation')
            ->set('authorDraft.institution', 'Unlisted Community Institute')
            ->call('saveAuthor')
            ->call('save')
            ->assertHasNoErrors();

        $author = Paper::query()->sole()->authors()->sole();
        $this->assertSame('Unlisted Community Institute', $author->institution);
        $this->assertNull($author->ror_id);
        $this->assertSame('manual', $author->affiliation_source);
    }

    public function test_manual_fallback_rejects_blank_affiliation(): void
    {
        $actor = User::factory()->create(['institution' => '']);

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->set('title', 'Educational Society and Sustainability')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->call('useManualAffiliation')
            ->call('saveAuthor')
            ->assertHasErrors('authorDraft.institution');

        $this->assertDatabaseCount('papers', 0);
    }

    public function test_invalid_ror_id_is_rejected_without_creating_a_draft(): void
    {
        $actor = User::factory()->create();

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->set('title', 'Education in Contemporary Civilization')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->set('authorDraft.affiliation_source', 'ror')
            ->set('authorDraft.ror_id', 'https://evil.example/not-a-ror')
            ->call('saveAuthor')
            ->assertHasErrors('authorDraft.ror_id');

        $this->assertDatabaseCount('papers', 0);
    }

    public function test_inactive_ror_record_cannot_be_marked_verified(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.ror.org/v2/organizations/041rf3s62' => Http::response($this->unisya('inactive'), 200),
        ]);

        $actor = User::factory()->create();

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->set('title', 'Islamic Education and Social Accountability')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->set('authorDraft.affiliation_source', 'ror')
            ->set('authorDraft.ror_id', 'https://ror.org/041rf3s62')
            ->call('saveAuthor')
            ->assertHasErrors('authorDraft.ror_id');

        $this->assertDatabaseCount('papers', 0);
    }

    public function test_ror_api_unavailable_keeps_the_paper_unsaved(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.ror.org/v2/organizations/*' => Http::response(['error' => 'unavailable'], 503),
        ]);

        $actor = User::factory()->create();

        Livewire::actingAs($actor)->test(PaperEditor::class)
            ->set('title', 'Educational Innovation and Institutional Ethics')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->set('authorDraft.affiliation_source', 'ror')
            ->set('authorDraft.ror_id', 'https://ror.org/041rf3s62')
            ->call('saveAuthor')
            ->assertHasErrors('authorDraft.ror_id');

        $this->assertDatabaseCount('papers', 0);
    }

    public function test_legacy_author_affiliation_stays_unverified_until_owner_reviews_it(): void
    {
        $actor = User::factory()->create();

        $paper = app(SavePaperDraft::class)->handle(
            $actor,
            null,
            ['title' => 'Historical Paper Draft with Manual Institution', 'conference_track_id' => $this->track()->id],
            [['name' => $actor->name, 'email' => $actor->email, 'institution' => 'Historical Institution', 'affiliation_source' => 'manual']],
            0,
        );

        $paper->authors()->update(['affiliation_source' => null, 'ror_id' => null]);

        Livewire::actingAs($actor)->test(PaperEditor::class, ['paper' => $paper])
            ->call('editAuthor', 0)
            ->assertSet('authorDraft.affiliation_source', 'legacy')
            ->assertSet('authorDraft.institution', 'Historical Institution')
            ->set('title', 'Historical Paper Draft with Manual Institution Revised')
            ->call('saveAuthor')
            ->assertHasErrors('authorDraft.affiliation_source');

        $this->assertSame('Historical Paper Draft with Manual Institution', $paper->fresh()->title);
    }

    public function test_empty_my_submissions_has_centered_action_without_disabled_pagination(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor)->get(route('papers.index'))
            ->assertOk()
            ->assertSee('No paper drafts found')
            ->assertSee('Start a draft')
            ->assertDontSee('New paper draft')
            ->assertDontSee('Page 1 of 1');
    }
}
