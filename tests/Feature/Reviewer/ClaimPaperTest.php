<?php

namespace Tests\Feature\Reviewer;

use App\Actions\Reviewer\ClaimPaper;
use App\Enums\PaperStatus;
use App\Enums\UserRole;
use App\Livewire\Reviewer\AvailablePapers;
use App\Livewire\Reviewer\PaperPreview;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\PaperReviewClaim;
use App\Models\User;
use App\Services\Reviewer\ReviewerPaperAccess;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ClaimPaperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
    }

    private function reviewer(): User
    {
        return User::factory()->create(['role' => UserRole::Reviewer]);
    }

    private function paper(
        User $owner,
        PaperStatus $status = PaperStatus::Submitted,
        string $title = 'Community Education and Islamic History',
        string $trackCode = 'TRK-01',
    ): Paper {
        $ulid = (string) Str::ulid();
        $path = "paper-abstracts/{$owner->id}/{$ulid}/abstract.pdf";
        Storage::disk('local')->put($path, "%PDF-1.4\n% safe test document");

        $paper = Paper::query()->forceCreate([
            'owner_id' => $owner->id,
            'public_id' => $ulid,
            'conference_track_id' => ConferenceTrack::query()->where('code', $trackCode)->firstOrFail()->id,
            'title' => $title,
            'status' => $status,
            'abstract_text' => 'Abstract text with historical educational evidence.',
            'keywords' => ['Islamic Education', 'Social History'],
            'abstract_pdf_path' => $path,
            'abstract_pdf_original_name' => 'abstract.pdf',
            'abstract_pdf_size' => 24,
            'abstract_pdf_uploaded_at' => now(),
            'submitted_at' => $status === PaperStatus::Submitted ? now() : null,
            'submission_version' => $status === PaperStatus::Submitted ? 1 : 0,
        ]);
        $paper->reference = sprintf('ICH27-P%06d', $paper->id);
        $paper->save();

        return $paper;
    }

    public function test_claim_is_uuid_v7_and_preserves_paper_state_and_identity(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $paper = $this->paper($author);
        $reference = $paper->reference;
        $paperId = $paper->id;
        $publicId = $paper->public_id;

        $claim = app(ClaimPaper::class)->handle($reviewer, $publicId);

        $this->assertSame(7, Str::isUuid($claim->id) ? (int) $claim->id[14] : 0);
        $this->assertSame($paperId, $claim->paper_id);
        $this->assertSame($paperId, $claim->active_paper_id);
        $this->assertSame($reviewer->id, $claim->reviewer_id);
        $this->assertNotNull($claim->claimed_at);
        $this->assertNull($claim->released_at);
        $this->assertSame(1, $paper->reviewClaims()->count());

        $paper->refresh();
        $this->assertSame($paperId, $paper->id);
        $this->assertSame($reference, $paper->reference);
        $this->assertSame($publicId, $paper->public_id);
        $this->assertSame(PaperStatus::Submitted, $paper->status);
        $this->assertSame(1, $paper->submission_version);
    }

    public function test_available_and_my_reviews_lists_update_after_confirm_claim(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $paper = $this->paper($author);

        Livewire::actingAs($reviewer)->test(AvailablePapers::class)
            ->assertSee('Claim paper '.$paper->reference)
            ->assertSee('Preview paper '.$paper->reference)
            ->call('askClaim', $paper->public_id)
            ->assertSet('claimModalOpen', true)
            ->assertSet('confirmingReference', $paper->reference)
            ->assertSee('Claim this abstract?')
            ->call('confirmClaim')
            ->assertHasNoErrors()
            ->assertSet('claimModalOpen', false)
            ->assertSet('tab', 'mine')
            ->assertSee($paper->reference)
            ->assertDontSee('Claim paper '.$paper->reference)
            ->assertSee('My Reviews');

        $this->assertSame(1, PaperReviewClaim::query()->count());
    }

    public function test_cancel_confirmation_creates_no_assignment(): void
    {
        $reviewer = $this->reviewer();
        $paper = $this->paper(User::factory()->create());

        Livewire::actingAs($reviewer)->test(AvailablePapers::class)
            ->call('askClaim', $paper->public_id)
            ->assertSet('claimModalOpen', true)
            ->call('cancelClaim')
            ->assertSet('claimModalOpen', false)
            ->assertSet('confirmingPublicId', null);

        $this->assertDatabaseCount('paper_review_claims', 0);
    }

    public function test_second_reviewer_cannot_claim_same_paper_and_cannot_preview_or_download_claimed_pdf(): void
    {
        $first = $this->reviewer();
        $second = $this->reviewer();
        $paper = $this->paper(User::factory()->create());
        app(ClaimPaper::class)->handle($first, $paper->public_id);

        $this->assertFalse(app(ReviewerPaperAccess::class)->isClaimable($paper, $second));

        Livewire::actingAs($second)->test(AvailablePapers::class)
            ->assertDontSee($paper->reference)
            ->call('askClaim', $paper->public_id)
            ->assertNotFound();

        $this->actingAs($second)->get(route('reviewer.papers.show', $paper->public_id))->assertNotFound();
        $this->get(route('reviewer.papers.pdf', $paper->public_id))->assertNotFound();

        $this->actingAs($first)->get(route('reviewer.papers.show', $paper->public_id))->assertOk();
        $this->get(route('reviewer.papers.pdf', $paper->public_id))->assertDownload();
    }

    public function test_stale_modal_does_not_overwrite_existing_claim(): void
    {
        $first = $this->reviewer();
        $second = $this->reviewer();
        $paper = $this->paper(User::factory()->create());

        $stale = Livewire::actingAs($first)->test(AvailablePapers::class)
            ->call('askClaim', $paper->public_id)
            ->assertSet('claimModalOpen', true);

        app(ClaimPaper::class)->handle($second, $paper->public_id);
        $stale->call('confirmClaim')->assertHasErrors('claim');

        $this->assertSame(1, $paper->reviewClaims()->whereNotNull('active_paper_id')->count());
        $this->assertSame($second->id, $paper->reviewClaims()->sole()->reviewer_id);
    }

    public function test_unique_constraint_prevents_double_active_assignment_at_database_level(): void
    {
        $paper = $this->paper(User::factory()->create());
        $first = $this->reviewer();
        $second = $this->reviewer();
        app(ClaimPaper::class)->handle($first, $paper->public_id);

        $this->expectException(UniqueConstraintViolationException::class);
        PaperReviewClaim::query()->create([
            'paper_id' => $paper->id,
            'active_paper_id' => $paper->id,
            'reviewer_id' => $second->id,
            'claimed_at' => now(),
        ]);
    }

    public function test_reviewer_cannot_claim_own_paper_or_a_paper_when_they_are_a_coauthor(): void
    {
        $reviewer = $this->reviewer();
        $ownPaper = $this->paper($reviewer);
        $otherPaper = $this->paper(User::factory()->create());

        $otherPaper->authors()->create([
            'name' => 'Co-author Confidential',
            'email' => strtoupper($reviewer->email),
            'institution' => 'Private University',
            'position' => 1,
            'is_corresponding' => true,
            'affiliation_source' => 'manual',
        ]);

        Livewire::actingAs($reviewer)->test(AvailablePapers::class)
            ->assertDontSee($ownPaper->reference)
            ->assertDontSee($otherPaper->reference);

        foreach ([$ownPaper, $otherPaper] as $paper) {
            $this->actingAs($reviewer)
                ->get(route('reviewer.papers.show', $paper->public_id))->assertNotFound();
            $this->get(route('reviewer.papers.pdf', $paper->public_id))->assertNotFound();

            try {
                app(ClaimPaper::class)->handle($reviewer, $paper->public_id);
                $this->fail('Authored paper was claimed despite conflict of interest.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('claim', $exception->errors());
            }
        }

        $this->assertDatabaseCount('paper_review_claims', 0);
    }

    public function test_nonreviewer_cannot_call_claim_action_even_with_known_paper_id(): void
    {
        $user = User::factory()->create();
        $paper = $this->paper(User::factory()->create());

        $this->expectException(AuthorizationException::class);
        app(ClaimPaper::class)->handle($user, $paper->public_id);
    }

    public function test_drafts_are_not_claimable_even_by_direct_action(): void
    {
        $reviewer = $this->reviewer();
        $draft = $this->paper(User::factory()->create(), PaperStatus::Draft);

        $this->assertFalse(app(ReviewerPaperAccess::class)->isClaimable($draft, $reviewer));
        $this->actingAs($reviewer)->get(route('reviewer.papers.show', $draft->public_id))->assertNotFound();

        try {
            app(ClaimPaper::class)->handle($reviewer, $draft->public_id);
            $this->fail('Draft was claimed');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('claim', $exception->errors());
        }

        $this->assertDatabaseCount('paper_review_claims', 0);
    }

    public function test_my_reviews_list_filters_only_own_assignments_without_author_names_and_affiliations(): void
    {
        $reviewer = $this->reviewer();
        $other = $this->reviewer();
        $author = User::factory()->create();
        $first = $this->paper($author, title: 'Traditions and Islamic Knowledge');
        $second = $this->paper($author, title: 'Education and Culture in East Java', trackCode: 'TRK-02');

        $first->authors()->create([
            'name' => 'Secret Author Name',
            'email' => 'secret-author@example.org',
            'institution' => 'Secret University Affiliation',
            'position' => 1,
            'is_corresponding' => true,
            'affiliation_source' => 'manual',
        ]);

        app(ClaimPaper::class)->handle($reviewer, $first->public_id);
        app(ClaimPaper::class)->handle($other, $second->public_id);

        Livewire::actingAs($reviewer)->test(AvailablePapers::class)
            ->call('selectTab', 'mine')
            ->assertSee($first->reference)
            ->assertDontSee($second->reference)
            ->assertDontSee('Secret Author Name')
            ->assertDontSee('Secret University Affiliation')
            ->assertSee('My Reviews');

        $this->actingAs($reviewer)->get(route('reviewer.papers.show', $first->public_id))
            ->assertOk()->assertDontSee('Secret Author Name')->assertDontSee('Secret University Affiliation');
    }

    public function test_claimed_abstract_can_be_previewed_but_review_form_not_yet_available(): void
    {
        $reviewer = $this->reviewer();
        $paper = $this->paper(User::factory()->create());
        app(ClaimPaper::class)->handle($reviewer, $paper->public_id);

        $this->actingAs($reviewer)->get(route('reviewer.papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Claimed by you')
            ->assertSee('Assigned to you')
            ->assertSee('REV-003')
            ->assertDontSee('Confirm claim');
        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
    }

    public function test_author_timeline_adds_real_claim_date_without_reviewer_identity_or_unpublished_decision(): void
    {
        $owner = User::factory()->create();
        $reviewer = $this->reviewer();
        $paper = $this->paper($owner);

        $this->actingAs($owner)
            ->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Draft created')
            ->assertSee('Paper submitted')
            ->assertDontSee('Abstract claimed');

        $claim = app(ClaimPaper::class)->handle($reviewer, $paper->public_id);
        $claimedDate = $claim->claimed_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB';

        $this->actingAs($owner)
            ->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertSeeInOrder(['Draft created', 'Paper submitted', 'Abstract claimed'])
            ->assertSee('Assigned for academic review')
            ->assertSee($claimedDate)
            ->assertDontSee($reviewer->name)
            ->assertDontSee($reviewer->email)
            ->assertDontSee('Review completed')
            ->assertDontSee('Decision published');

        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
        $this->assertSame(1, $paper->reviewClaims()->count());
    }

    public function test_reviewer_workflow_shows_real_submission_and_claim_dates_and_only_future_review_placeholder(): void
    {
        $owner = User::factory()->create();
        $reviewer = $this->reviewer();
        $paper = $this->paper($owner);
        $submissionDate = $paper->submitted_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB';

        $this->actingAs($reviewer)
            ->get(route('reviewer.papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Paper submitted')
            ->assertSee($submissionDate)
            ->assertSee('Not claimed yet')
            ->assertDontSee('Claimed by you')
            ->assertSee('Not yet available')
            ->assertDontSee($owner->name);

        $claim = app(ClaimPaper::class)->handle($reviewer, $paper->public_id);
        $claimedDate = $claim->claimed_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB';

        $this->actingAs($reviewer)
            ->get(route('reviewer.papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Paper submitted')
            ->assertSee($submissionDate)
            ->assertSee('Claimed by you')
            ->assertSee($claimedDate)
            ->assertDontSee('Not claimed yet')
            ->assertSee('Not yet available')
            ->assertSee('REV-003');
    }

    public function test_author_timeline_uses_recorded_release_date_and_does_not_fabricate_review_completion(): void
    {
        $owner = User::factory()->create();
        $reviewer = $this->reviewer();
        $paper = $this->paper($owner);
        $claim = app(ClaimPaper::class)->handle($reviewer, $paper->public_id);

        $releasedAt = now()->addMinutes(10);
        $claim->forceFill([
            'active_paper_id' => null,
            'released_at' => $releasedAt,
            'released_by' => $reviewer->id,
            'release_reason' => 'Test-only historical release',
        ])->save();

        $this->actingAs($owner)
            ->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Abstract claimed')
            ->assertSee('Reviewer assignment released')
            ->assertSee($releasedAt->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB')
            ->assertDontSee($reviewer->name)
            ->assertDontSee('Review completed');

        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
    }

    public function test_unpublished_internal_recommendation_is_not_exposed_to_author(): void
    {
        $owner = User::factory()->create();
        $reviewer = $this->reviewer();
        $paper = $this->paper($owner);
        app(ClaimPaper::class)->handle($reviewer, $paper->public_id);

        $paper->statusHistories()->create([
            'actor_id' => $reviewer->id,
            'event' => 'internal_recommendation_accept',
            'from_status' => PaperStatus::Submitted->value,
            'to_status' => PaperStatus::Submitted->value,
            'submission_version' => 1,
            'occurred_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Abstract claimed')
            ->assertDontSee('Internal recommendation accept')
            ->assertDontSee('internal_recommendation_accept')
            ->assertDontSee('Decision published');
    }

    public function test_claim_preview_dialog_can_open_cancel_and_confirm(): void
    {
        $reviewer = $this->reviewer();
        $paper = $this->paper(User::factory()->create());

        Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper])
            ->assertSee('Claim this abstract')
            ->call('askClaim')
            ->assertSet('claimModalOpen', true)
            ->call('cancelClaim')
            ->assertSet('claimModalOpen', false)
            ->call('askClaim')
            ->call('confirmClaim')
            ->assertHasNoErrors()
            ->assertRedirect(route('reviewer.workspace', ['tab' => 'mine']));

        $this->assertSame(1, $paper->reviewClaims()->count());
    }

    public function test_no_claim_created_when_confirmation_state_is_forged_for_a_coauthored_paper(): void
    {
        $reviewer = $this->reviewer();
        $paper = $this->paper(User::factory()->create());
        $paper->authors()->create([
            'name' => 'Co Author',
            'email' => $reviewer->email,
            'institution' => 'University of Example',
            'position' => 1,
            'is_corresponding' => true,
            'affiliation_source' => 'manual',
        ]);

        Livewire::actingAs($reviewer)->test(AvailablePapers::class)
            ->set('claimModalOpen', true)
            ->set('confirmingPublicId', $paper->public_id)
            ->call('confirmClaim')
            ->assertHasErrors('claim');

        $this->assertDatabaseCount('paper_review_claims', 0);
    }
}
