<?php

namespace Tests\Feature\Reviewer;

use App\Actions\Reviewer\ClaimPaper;
use App\Actions\Reviewer\SavePaperReview;
use App\Enums\PaperReviewRecommendation;
use App\Enums\PaperReviewStatus;
use App\Enums\PaperStatus;
use App\Enums\UserRole;
use App\Livewire\Reviewer\AvailablePapers;
use App\Livewire\Reviewer\PaperPreview;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\PaperReview;
use App\Models\User;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PaperReviewWorkflowTest extends TestCase
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

    private function paper(User $owner): Paper
    {
        $ulid = (string) Str::ulid();
        $path = "paper-abstracts/{$owner->id}/{$ulid}/abstract.pdf";
        Storage::disk('local')->put($path, "%PDF-1.4\n% test PDF");

        $paper = Paper::query()->forceCreate([
            'owner_id' => $owner->id,
            'public_id' => $ulid,
            'conference_track_id' => ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail()->id,
            'title' => 'Abstract for Academic Review',
            'status' => PaperStatus::Submitted,
            'abstract_text' => 'Research findings on community and education.',
            'keywords' => ['Community', 'Education'],
            'abstract_pdf_path' => $path,
            'abstract_pdf_original_name' => 'abstract.pdf',
            'abstract_pdf_size' => 22,
            'abstract_pdf_uploaded_at' => now(),
            'submitted_at' => now(),
            'submission_version' => 1,
        ]);

        $paper->reference = sprintf('ICH27-P%06d', $paper->id);
        $paper->save();

        return $paper;
    }

    private function assigned(User $owner, ?User $reviewer = null): array
    {
        $reviewer ??= $this->reviewer();
        $paper = $this->paper($owner);
        $claim = app(ClaimPaper::class)->handle($reviewer, $paper->public_id);

        return [$paper, $reviewer, $claim];
    }

    public function test_claimed_reviewer_can_save_incomplete_review_draft_with_uuid_v7_and_audit(): void
    {
        [$paper, $reviewer, $claim] = $this->assigned(User::factory()->create());

        Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper])
            ->assertSee('Abstract review')
            ->assertSee('General Comments')
            ->assertSee('Accept')
            ->assertSee('Reject')
            ->assertDontSee('Revisions Required')
            ->call('saveReviewDraft')
            ->assertHasNoErrors()
            ->assertSee('Your review draft was saved');

        $review = PaperReview::query()->sole();
        $this->assertSame($claim->id, $review->paper_review_claim_id);
        $this->assertSame(7, Str::isUuid($review->id) ? (int) $review->id[14] : 0);
        $this->assertSame(PaperReviewStatus::Draft, $review->status);
        $this->assertNull($review->recommendation);
        $this->assertNull($review->general_comments);
        $this->assertNull($review->submitted_at);
        $this->assertSame(1, $review->lock_version);
        $event = $review->events()->sole();
        $this->assertSame('draft_saved', $event->event);
        $this->assertSame(1, $event->version);
        $this->assertSame(7, Str::isUuid($event->id) ? (int) $event->id[14] : 0);
        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
    }

    public function test_saving_draft_multiple_times_persists_comments_and_recommendation_without_submitting(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());

        Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper])
            ->set('generalComments', '  Please clarify the study design in the full paper.  ')
            ->call('saveReviewDraft')
            ->assertSet('reviewVersion', 1)
            ->set('recommendation', 'accept')
            ->call('saveReviewDraft')
            ->assertHasNoErrors()
            ->assertSet('reviewVersion', 2);

        $review = PaperReview::query()->sole();
        $this->assertSame('Please clarify the study design in the full paper.', $review->general_comments);
        $this->assertSame(PaperReviewRecommendation::Accept, $review->recommendation);
        $this->assertSame(PaperReviewStatus::Draft, $review->status);
        $this->assertSame(2, $review->events()->count());

        $this->actingAs($reviewer)->get(route('reviewer.papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Review draft saved')
            ->assertSee('Review Draft');
    }

    public function test_accept_review_is_submitted_once_after_confirmation_and_shown_as_readonly(): void
    {
        $owner = User::factory()->create();
        [$paper, $reviewer, $claim] = $this->assigned($owner);
        $id = $paper->id;
        $reference = $paper->reference;
        $publicId = $paper->public_id;

        Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper])
            ->set('generalComments', 'Accepted. Please expand the methods when submitting the full paper.')
            ->set('recommendation', 'accept')
            ->call('askSubmitReview')
            ->assertSet('submitReviewModalOpen', true)
            ->assertSee('Submit your review?')
            ->call('confirmSubmitReview')
            ->assertHasNoErrors()
            ->assertSet('submitReviewModalOpen', false)
            ->assertSee('Review Submitted')
            ->assertSee('Review completed')
            ->assertDontSee('Save Draft')
            ->assertDontSee('Confirm Submit Review');

        $review = PaperReview::query()->sole();
        $this->assertSame($claim->id, $review->paper_review_claim_id);
        $this->assertSame(PaperReviewStatus::Submitted, $review->status);
        $this->assertSame(PaperReviewRecommendation::Accept, $review->recommendation);
        $this->assertNotNull($review->submitted_at);
        $this->assertSame(1, $review->lock_version);
        $this->assertSame('review_submitted', $review->events()->sole()->event);

        $paper->refresh();
        $this->assertSame($id, $paper->id);
        $this->assertSame($reference, $paper->reference);
        $this->assertSame($publicId, $paper->public_id);
        $this->assertSame(PaperStatus::Submitted, $paper->status);
        $this->assertSame(1, $paper->submission_version);
    }

    public function test_reject_is_an_internal_recommendation_and_author_only_sees_review_completed(): void
    {
        $owner = User::factory()->create();
        [$paper, $reviewer] = $this->assigned($owner);
        $comment = 'The conceptual contribution is not sufficiently supported.';

        $review = app(SavePaperReview::class)->handle($reviewer, $paper->public_id, $comment, 'reject', 0, true);
        $this->assertSame(PaperReviewRecommendation::Reject, $review->recommendation);

        $date = $review->submitted_at->copy()->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB';

        $this->actingAs($owner)->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertSeeInOrder(['Draft created', 'Paper submitted', 'Abstract claimed', 'Review completed'])
            ->assertSee($date)
            ->assertDontSee($reviewer->name)
            ->assertDontSee($reviewer->email)
            ->assertDontSee($comment)
            ->assertDontSee('Recommendation')
            ->assertDontSee('Reject')
            ->assertDontSee('Decision published');

        $this->actingAs($reviewer)->get(route('reviewer.papers.show', $paper->public_id))
            ->assertOk()->assertSee('Reject')->assertSee($comment)->assertSee($date);
    }

    public function test_submit_requires_nonblank_comments_and_accept_or_reject_but_save_draft_does_not(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());

        Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper])
            ->call('askSubmitReview')
            ->assertHasErrors(['generalComments', 'recommendation'])
            ->assertSet('submitReviewModalOpen', false)
            ->set('generalComments', ' ')
            ->set('recommendation', 'reject')
            ->call('askSubmitReview')
            ->assertHasErrors(['generalComments'])
            ->assertSet('submitReviewModalOpen', false);

        $this->assertDatabaseCount('paper_reviews', 0);

        try {
            app(SavePaperReview::class)->handle($reviewer, $paper->public_id, ' ', null, 0, true);
            $this->fail('Blank review cannot be submitted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('generalComments', $exception->errors());
            $this->assertArrayHasKey('recommendation', $exception->errors());
        }

        $this->assertDatabaseCount('paper_review_events', 0);
    }

    public function test_revisions_recommendation_is_rejected_even_for_draft_and_direct_actions(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());

        foreach (['revision', 'revisions_required', 'revise', 'accepted', 'rejected'] as $invalid) {
            try {
                app(SavePaperReview::class)->handle($reviewer, $paper->public_id, 'General comments', $invalid, 0);
                $this->fail('Invalid recommendation was accepted: '.$invalid);
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('recommendation', $exception->errors());
            }
        }

        $this->assertDatabaseCount('paper_reviews', 0);
    }

    public function test_submitted_review_cannot_be_modified_or_resubmitted_directly(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());

        $review = app(SavePaperReview::class)->handle($reviewer, $paper->public_id, 'Final general comments', 'accept', 0, true);

        foreach ([false, true] as $submit) {
            try {
                app(SavePaperReview::class)->handle($reviewer, $paper->public_id, 'Tampered content', 'reject', 1, $submit);
                $this->fail('Submitted review was editable.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('review', $exception->errors());
            }
        }

        $review->refresh();
        $this->assertSame('Final general comments', $review->general_comments);
        $this->assertSame(PaperReviewRecommendation::Accept, $review->recommendation);
        $this->assertSame(1, $review->events()->count());
    }

    public function test_stale_browser_tab_is_rejected_without_overwriting_saved_review(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());

        $first = Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper]);
        $stale = Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper]);

        $first->set('generalComments', 'Most recent saved comments.')
            ->call('saveReviewDraft')->assertSet('reviewVersion', 1);

        $stale->set('generalComments', 'Obsolete comments from an older tab.')
            ->call('saveReviewDraft')->assertHasErrors('review');

        $this->assertSame('Most recent saved comments.', PaperReview::query()->sole()->general_comments);
        $this->assertSame(1, PaperReview::query()->sole()->lock_version);
    }

    public function test_cancel_submit_confirmation_does_not_create_or_change_review(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());

        Livewire::actingAs($reviewer)->test(PaperPreview::class, ['paper' => $paper])
            ->set('generalComments', 'A substantive comment.')
            ->set('recommendation', 'reject')
            ->call('askSubmitReview')
            ->assertSet('submitReviewModalOpen', true)
            ->call('cancelSubmitReview')
            ->assertSet('submitReviewModalOpen', false);

        $this->assertDatabaseCount('paper_reviews', 0);
    }

    public function test_other_reviewer_author_and_nonreviewer_cannot_modify_assigned_review(): void
    {
        $owner = User::factory()->create();
        [$paper, $reviewer] = $this->assigned($owner);
        $other = $this->reviewer();

        try {
            app(SavePaperReview::class)->handle($other, $paper->public_id, 'Private reviewer comments', 'accept', 0, true);
            $this->fail('Other reviewer should not be able to save.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }

        $this->expectException(AuthorizationException::class);
        app(SavePaperReview::class)->handle($owner, $paper->public_id, 'Author attempting tamper', 'accept', 0, true);
    }

    public function test_released_claim_blocks_save_and_submit_and_does_not_change_paper_status(): void
    {
        [$paper, $reviewer, $claim] = $this->assigned(User::factory()->create());
        $claim->forceFill(['active_paper_id' => null, 'released_at' => now(), 'released_by' => $reviewer->id, 'release_reason' => 'test'])->save();

        $this->expectException(ModelNotFoundException::class);
        app(SavePaperReview::class)->handle($reviewer, $paper->public_id, 'This should never be saved', 'accept', 0, true);
    }

    public function test_my_reviews_reports_claimed_draft_and_submitted_without_exposing_authors(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $claimed = $this->paper($author);
        $draft = $this->paper($author);
        $submitted = $this->paper($author);

        foreach ([$claimed, $draft, $submitted] as $paper) {
            app(ClaimPaper::class)->handle($reviewer, $paper->public_id);
        }

        $draftReview = app(SavePaperReview::class)->handle($reviewer, $draft->public_id, 'Needs some clarification.', null, 0);
        $submittedReview = app(SavePaperReview::class)->handle($reviewer, $submitted->public_id, 'Accept with full paper suggestions.', 'accept', 0, true);

        Livewire::actingAs($reviewer)->test(AvailablePapers::class, ['tab' => 'mine'])
            ->assertSee($claimed->reference)
            ->assertSee($draft->reference)
            ->assertSee($submitted->reference)
            ->assertSee('Claimed')
            ->assertSee('Review Draft')
            ->assertSee('Review Submitted')
            ->assertDontSee($author->name)
            ->assertDontSee($author->email);

        $this->assertSame(PaperReviewStatus::Draft, $draftReview->fresh()->status);
        $this->assertSame(PaperReviewStatus::Submitted, $submittedReview->fresh()->status);
    }

    public function test_late_coauthor_conflict_blocks_review_even_after_successful_claim(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());
        $paper->authors()->create([
            'name' => 'Added Coauthor',
            'email' => strtoupper($reviewer->email),
            'institution' => 'Example University',
            'position' => 1,
            'is_corresponding' => true,
            'affiliation_source' => 'manual',
        ]);

        $this->actingAs($reviewer)
            ->get(route('reviewer.papers.show', $paper->public_id))
            ->assertNotFound();

        try {
            app(SavePaperReview::class)->handle($reviewer, $paper->public_id, 'Forbidden review', 'accept', 0, true);
            $this->fail('New coauthor identity should have blocked the review.');
        } catch (NotFoundHttpException) {
            $this->assertDatabaseCount('paper_reviews', 0);
        }
    }

    public function test_paper_status_change_blocks_review_without_writing_any_record(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());
        $paper->forceFill(['status' => PaperStatus::Draft])->save();

        try {
            app(SavePaperReview::class)->handle($reviewer, $paper->public_id, 'Cannot proceed', 'reject', 0, true);
            $this->fail('Review was submitted against a non-Submitted paper.');
        } catch (NotFoundHttpException) {
            $this->assertDatabaseCount('paper_reviews', 0);
        }
    }

    public function test_general_comments_reject_above_ten_thousand_characters_on_draft_and_submit(): void
    {
        [$paper, $reviewer] = $this->assigned(User::factory()->create());
        foreach ([false, true] as $submit) {
            try {
                app(SavePaperReview::class)->handle($reviewer, $paper->public_id, str_repeat('x', 10001), 'accept', 0, $submit);
                $this->fail('Oversized comments must fail.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('generalComments', $exception->errors());
            }
        }
        $this->assertDatabaseCount('paper_reviews', 0);
    }

    public function test_review_submission_does_not_send_email_or_publish_decision(): void
    {
        Mail::fake();
        Notification::fake();
        [$paper, $reviewer] = $this->assigned(User::factory()->create());

        app(SavePaperReview::class)->handle($reviewer, $paper->public_id, 'Complete comments.', 'accept', 0, true);

        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
        $this->assertSame(1, $paper->fresh()->submission_version);
        $this->assertDatabaseCount('paper_status_histories', 0);
        $this->assertDatabaseCount('paper_review_events', 1);
        Mail::assertNothingOutgoing();
        Notification::assertNothingSent();
    }
}
