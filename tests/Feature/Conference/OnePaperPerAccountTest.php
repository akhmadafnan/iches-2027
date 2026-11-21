<?php

namespace Tests\Feature\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Enums\PaperStatus;
use App\Enums\RegistrationIntention;
use App\Livewire\Conference\MyPapers;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OnePaperPerAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
    }

    /**
     * @param  list<array{name: string, email: string, institution: string, affiliation_source: string}>|null  $authors
     */
    private function draft(User $owner, ?array $authors = null): Paper
    {
        $track = ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail();

        return app(SavePaperDraft::class)->handle(
            $owner,
            null,
            ['title' => 'One Account One Conference Paper', 'conference_track_id' => $track->id],
            $authors ?? [[
                'name' => $owner->name,
                'email' => $owner->email,
                'institution' => 'University of Example',
                'affiliation_source' => 'manual',
            ]],
            0,
        );
    }

    public function test_fresh_account_can_create_one_draft_but_policy_blocks_any_second_owned_paper(): void
    {
        $owner = User::factory()->create();

        $this->assertTrue(Gate::forUser($owner)->allows('create', Paper::class));
        $paper = $this->draft($owner);

        $this->assertFalse(Gate::forUser($owner)->allows('create', Paper::class));

        try {
            $this->draft($owner);
            $this->fail('A second paper must be denied by the server policy.');
        } catch (AuthorizationException $exception) {
            $this->assertSame('This action is unauthorized.', $exception->getMessage());
        }

        $this->assertDatabaseCount('papers', 1);
        $this->assertSame($paper->reference, $owner->papers()->sole()->reference);
    }

    public function test_direct_database_insert_cannot_bypass_unique_owner_constraint(): void
    {
        $owner = User::factory()->create();
        $paper = $this->draft($owner);

        try {
            DB::table('papers')->insert([
                'owner_id' => $owner->id,
                'conference_track_id' => $paper->conference_track_id,
                'public_id' => (string) Str::ulid(),
                'reference' => 'ICH27-P999999',
                'title' => 'Attempted Second Paper',
                'status' => PaperStatus::Draft->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->fail('Unique owner constraint must reject a direct duplicate insert.');
        } catch (UniqueConstraintViolationException) {
            $this->assertDatabaseCount('papers', 1);
            $this->assertSame($paper->public_id, Paper::query()->sole()->public_id);
        }
    }

    public function test_existing_draft_create_route_redirects_to_edit_without_new_paper(): void
    {
        $owner = User::factory()->create();
        $paper = $this->draft($owner);

        $this->actingAs($owner)
            ->get(route('papers.create'))
            ->assertRedirect(route('papers.edit', $paper->public_id));

        $this->assertDatabaseCount('papers', 1);
    }

    public function test_submitted_owner_create_route_redirects_to_read_only_paper(): void
    {
        $owner = User::factory()->create();
        $paper = $this->draft($owner);
        $paper->status = PaperStatus::Submitted;
        $paper->save();

        $this->actingAs($owner)
            ->get(route('papers.create'))
            ->assertRedirect(route('papers.show', $paper->public_id));

        $this->assertFalse(Gate::forUser($owner)->allows('create', Paper::class));
        $this->assertDatabaseCount('papers', 1);
    }

    public function test_my_submissions_only_offers_a_new_draft_to_an_account_without_a_paper(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('papers.index'))
            ->assertOk()
            ->assertSee('Start a draft');

        $paper = $this->draft($owner);

        $this->actingAs($owner)
            ->get(route('papers.index'))
            ->assertOk()
            ->assertSee($paper->reference)
            ->assertDontSee('Start a draft')
            ->assertDontSee('New paper draft');

        Livewire::actingAs($owner)->test(MyPapers::class)
            ->set('search', 'no matching title found')
            ->assertSee('No matching papers')
            ->assertDontSee('Start a draft');
    }

    public function test_dashboard_cta_matches_no_paper_existing_draft_and_submitted_paper(): void
    {
        $owner = User::factory()->create([
            'registration_intention' => RegistrationIntention::SubmitPaper,
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertSee('Start a paper draft')
            ->assertSee(route('papers.create'), false);

        $paper = $this->draft($owner);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertSee('Continue your paper draft')
            ->assertSee('Continue draft')
            ->assertSee(route('papers.edit', $paper->public_id), false)
            ->assertDontSee('Start a paper draft');

        $paper->status = PaperStatus::Submitted;
        $paper->save();

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertSee('Track your submitted paper')
            ->assertSee('View submitted paper')
            ->assertSee(route('papers.show', $paper->public_id), false)
            ->assertDontSee('Start a paper draft');
    }

    public function test_coauthorship_on_another_paper_does_not_consume_own_paper_quota(): void
    {
        $collaborator = User::factory()->create();
        $firstOwner = User::factory()->create();

        $otherPaper = $this->draft($firstOwner, [
            [
                'name' => $firstOwner->name,
                'email' => $firstOwner->email,
                'institution' => 'University of Example',
                'affiliation_source' => 'manual',
            ],
            [
                'name' => $collaborator->name,
                'email' => $collaborator->email,
                'institution' => 'Research Partner Institute',
                'affiliation_source' => 'manual',
            ],
        ]);

        $this->assertTrue(Gate::forUser($collaborator)->allows('create', Paper::class));

        $ownPaper = $this->draft($collaborator);

        $this->assertNotSame($otherPaper->owner_id, $ownPaper->owner_id);
        $this->assertSame(2, Paper::query()->count());
        $this->assertSame(1, $collaborator->papers()->count());
        $this->assertTrue($otherPaper->authors()->where('email', $collaborator->email)->exists());
    }

    public function test_an_existing_owner_may_still_edit_draft_without_changing_identity(): void
    {
        $owner = User::factory()->create();
        $paper = $this->draft($owner);

        $edited = app(SavePaperDraft::class)->handle(
            $owner,
            $paper,
            ['title' => 'Updated Own Conference Paper', 'conference_track_id' => $paper->conference_track_id],
            [[
                'name' => $owner->name,
                'email' => $owner->email,
                'institution' => 'University of Example',
                'affiliation_source' => 'manual',
            ]],
            0,
        );

        $this->assertSame($paper->id, $edited->id);
        $this->assertSame($paper->public_id, $edited->public_id);
        $this->assertSame($paper->reference, $edited->reference);
        $this->assertDatabaseCount('papers', 1);
    }
}
