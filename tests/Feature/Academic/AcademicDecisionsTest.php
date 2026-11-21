<?php

namespace Tests\Feature\Academic;

use App\Actions\Academic\CorrectAcademicDecision;
use App\Actions\Academic\PublishAcademicDecisions;
use App\Actions\Academic\RetryDecisionNotification;
use App\Enums\PaperReviewRecommendation;
use App\Enums\PaperReviewStatus;
use App\Enums\PaperStatus;
use App\Enums\StaffDuty;
use App\Enums\UserRole;
use App\Jobs\SendDecisionNotification;
use App\Livewire\Academic\AcademicDecisions;
use App\Mail\AcademicDecisionMail;
use App\Models\ConferenceTrack;
use App\Models\DecisionNotification;
use App\Models\Paper;
use App\Models\PaperReview;
use App\Models\PaperReviewClaim;
use App\Models\User;
use App\Services\Academic\ReadyAcademicDecisions;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AcademicDecisionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config()->set('queue.default', 'database');
        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
    }

    private function authority(UserRole $role = UserRole::Committee): User
    {
        $actor = User::factory()->create(['role' => $role]);
        $actor->staffAssignments()->create([
            'duty' => StaffDuty::AcademicAuthority, 'scope_type' => 'global', 'scope_id' => 0,
        ]);

        return $actor;
    }

    private function paper(PaperStatus $status = PaperStatus::Submitted): Paper
    {
        $owner = User::factory()->create();
        $ulid = (string) Str::ulid();
        $path = "paper-abstracts/{$owner->id}/{$ulid}/abstract.pdf";
        Storage::disk('local')->put($path, '%PDF-1.4 fake test PDF');

        $paper = Paper::query()->forceCreate([
            'owner_id' => $owner->id, 'public_id' => $ulid,
            'conference_track_id' => ConferenceTrack::query()->firstOrFail()->id,
            'title' => 'Cultural and Education Conference Paper', 'status' => $status,
            'abstract_text' => 'A documented evidence-based abstract.',
            'keywords' => ['Education', 'Community'], 'abstract_pdf_path' => $path,
            'abstract_pdf_original_name' => 'abstract.pdf', 'abstract_pdf_size' => 23,
            'abstract_pdf_uploaded_at' => now(),
            'submitted_at' => $status === PaperStatus::Submitted ? now() : null,
            'submission_version' => $status === PaperStatus::Submitted ? 1 : 0,
        ]);
        $paper->reference = sprintf('ICH27-P%06d', $paper->id);
        $paper->save();

        return $paper;
    }

    private function reviewed(string $choice = 'accept', string $comments = 'Please clarify the research setting.'): Paper
    {
        $paper = $this->paper();
        $reviewer = User::factory()->create(['role' => UserRole::Reviewer]);
        $claim = PaperReviewClaim::query()->create([
            'paper_id' => $paper->id, 'reviewer_id' => $reviewer->id,
            'active_paper_id' => $paper->id, 'claimed_at' => now(),
        ]);
        PaperReview::query()->create([
            'paper_review_claim_id' => $claim->id, 'paper_id' => $paper->id,
            'reviewer_id' => $reviewer->id, 'general_comments' => $comments,
            'recommendation' => PaperReviewRecommendation::from($choice),
            'status' => PaperReviewStatus::Submitted, 'lock_version' => 1,
            'submitted_at' => now(),
        ]);

        return $paper;
    }

    public function test_only_assigned_authority_can_open_and_publish(): void
    {
        $paper = $this->reviewed();
        $unassigned = User::factory()->create(['role' => UserRole::Admin]);
        $this->get(route('academic.decisions'))->assertRedirect(route('login'));
        $this->actingAs($unassigned)->get(route('academic.decisions'))->assertForbidden();
        $this->actingAs($unassigned)->get(route('dashboard'))->assertDontSee('Open Academic Decisions');

        $this->expectException(AuthorizationException::class);
        app(PublishAcademicDecisions::class)->handle($unassigned, [$paper->public_id]);
    }

    public function test_assigned_committee_and_admin_can_see_ready_workspace(): void
    {
        $paper = $this->reviewed();
        foreach ([UserRole::Committee, UserRole::Admin] as $role) {
            $actor = $this->authority($role);
            $this->actingAs($actor)->get(route('academic.decisions'))
                ->assertOk()->assertSee('Academic Decisions')
                ->assertSee($paper->reference)->assertSee('Publish All Ready');
            $this->actingAs($actor)->get(route('dashboard'))->assertSee('Open Academic Decisions');
        }
    }

    public function test_only_active_final_reviews_are_eligible(): void
    {
        $ready = $this->reviewed();
        $draft = $this->paper(PaperStatus::Draft);
        $unreviewed = $this->paper();
        $released = $this->reviewed();
        $released->reviewClaims()->update(['active_paper_id' => null, 'released_at' => now()]);
        $ids = app(ReadyAcademicDecisions::class)->query()->pluck('public_id')->all();

        $this->assertSame([$ready->public_id], $ids);
        $this->assertNotContains($draft->public_id, $ids);
        $this->assertNotContains($unreviewed->public_id, $ids);
        $this->assertNotContains($released->public_id, $ids);
    }

    public function test_bulk_publish_automatically_shares_general_comments_for_both_accept_and_reject(): void
    {
        $actor = $this->authority();
        $accepted = $this->reviewed('accept');
        $rejected = $this->reviewed('reject');
        $result = app(PublishAcademicDecisions::class)->handle($actor, [$accepted->public_id, $rejected->public_id]);

        $this->assertSame(['published' => 2, 'skipped' => 0, 'failed' => 0, 'notifications' => 2], $result);
        $this->assertSame(PaperStatus::Accepted, $accepted->fresh()->status);
        $this->assertSame(PaperStatus::Rejected, $rejected->fresh()->status);
        $this->assertSame('Please clarify the research setting.', $accepted->fresh()->decision->public_comments);
        $this->assertSame('Please clarify the research setting.', $rejected->fresh()->decision->public_comments);
        $this->assertSame('Please clarify the research setting.', $rejected->decisionNotifications()->firstOrFail()->public_comments);
        $this->assertDatabaseCount('paper_decisions', 2);
        $this->assertDatabaseCount('paper_decision_events', 2);
        $this->assertDatabaseCount('decision_notifications', 2);
        $this->assertSame($accepted->owner->email, $accepted->decisionNotifications()->firstOrFail()->recipient_email);
    }

    public function test_rejected_paper_index_separates_published_status_from_updated_date(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed('reject');
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);

        $this->actingAs($paper->owner)->get(route('papers.index'))
            ->assertOk()
            ->assertSee($paper->reference)
            ->assertSee('Published Rejected')
            ->assertSee('Published decision')
            ->assertSee('w-[180px]')
            ->assertSee('whitespace-nowrap')
            ->assertSee($paper->fresh()->updated_at->timezone('Asia/Jakarta')->format('d M Y'));
    }

    public function test_repeated_publication_skips_existing_paper_without_extra_email(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        $action = app(PublishAcademicDecisions::class);
        $this->assertSame(1, $action->handle($actor, [$paper->public_id])['published']);
        $second = $action->handle($actor, [$paper->public_id, $paper->public_id]);

        $this->assertSame(0, $second['published']);
        $this->assertSame(1, $second['skipped']);
        $this->assertDatabaseCount('paper_decisions', 1);
        $this->assertDatabaseCount('decision_notifications', 1);
    }

    public function test_missing_override_reason_rejects_record_instead_of_publishing_default(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed('reject');
        $result = app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id], [
            $paper->public_id => ['outcome' => 'accepted', 'reason' => 'short'],
        ]);
        $this->assertSame(0, $result['published']);
        $this->assertSame(1, $result['failed']);
        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
        $this->assertDatabaseCount('paper_decisions', 0);
    }

    public function test_override_is_audited_without_mutating_reviewer_recommendation_or_leaking_reason(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed('reject', 'Review feedback included automatically.');
        $secret = 'Private internal rationale: the academic authority rechecked the evidence.';
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id], [
            $paper->public_id => ['outcome' => 'accepted', 'reason' => $secret],
        ]);

        $event = $paper->fresh()->decision->events()->sole();
        $this->assertSame('reject', $event->reviewer_recommendation);
        $this->assertSame($secret, $event->internal_reason);
        $this->assertSame('accepted', $event->to_outcome);
        $this->assertSame(PaperReviewRecommendation::Reject, $paper->reviews()->sole()->recommendation);

        $reviewer = $paper->reviews()->sole()->reviewer;
        $this->actingAs($paper->owner)->get(route('papers.show', $paper->public_id))
            ->assertOk()->assertSee('Published academic decision')->assertSee('Review feedback included automatically.')
            ->assertDontSee($secret)->assertDontSee($reviewer->email)
            ->assertDontSee($reviewer->name);
        $this->actingAs($paper->owner)->get(route('papers.index'))
            ->assertOk()
            ->assertSee('Published Accepted')
            ->assertSee('Published decision')
            ->assertSee('table-fixed')
            ->assertSee('w-[180px]');
        $this->actingAs(User::factory()->create())->get(route('papers.show', $paper->public_id))->assertForbidden();
    }

    public function test_correction_preserves_history_and_new_version_is_notified(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);
        $decision = app(CorrectAcademicDecision::class)->handle(
            $actor, $paper->public_id, 'rejected',
            'The committee reviewed newly available academic evidence.', 1,
        );

        $this->assertSame(2, $decision->version);
        $this->assertSame(PaperStatus::Rejected, $paper->fresh()->status);
        $this->assertSame(['published', 'corrected'], $decision->events()->pluck('event_type')->all());
        $this->assertSame('accepted', $decision->events()->where('event_type', 'corrected')->firstOrFail()->from_outcome);
        $this->assertDatabaseCount('paper_decisions', 1);
        $this->assertDatabaseCount('paper_decision_events', 2);
        $this->assertDatabaseCount('decision_notifications', 2);
        $this->assertSame('corrected', $paper->decisionNotifications()->firstOrFail()->event_type);
        $this->actingAs($paper->owner)->get(route('papers.show', $paper->public_id))
            ->assertSee('Published decision corrected')
            ->assertDontSee('The committee reviewed newly available academic evidence.');
    }

    public function test_stale_or_noop_correction_cannot_duplicate_notifications(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);

        try {
            app(CorrectAcademicDecision::class)->handle($actor, $paper->public_id, 'accepted', 'The reason for correction is not meaningful here.', 1);
            $this->fail('No-op correction accepted.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('correction', $e->errors());
        }

        app(CorrectAcademicDecision::class)->handle($actor, $paper->public_id, 'rejected', 'Corrections require audit reasons.', 1);
        try {
            app(CorrectAcademicDecision::class)->handle($actor, $paper->public_id, 'accepted', 'Stale browser state is never accepted.', 1);
            $this->fail('Stale correction accepted.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('correction', $e->errors());
        }
        $this->assertDatabaseCount('decision_notifications', 2);
    }

    public function test_mail_job_is_personal_idempotent_and_changes_queued_to_sent(): void
    {
        Mail::fake();
        $actor = $this->authority();
        $paper = $this->reviewed('accept', 'Public-safe comments.');
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);
        $outbox = DecisionNotification::query()->sole();
        (new SendDecisionNotification($outbox->id))->handle();
        (new SendDecisionNotification($outbox->id))->handle();

        $this->assertSame('sent', $outbox->fresh()->status);
        $this->assertSame(1, $outbox->fresh()->attempts);
        Mail::assertSent(AcademicDecisionMail::class, 1);
        Mail::assertSent(AcademicDecisionMail::class, fn (AcademicDecisionMail $mail) => $mail->hasTo($paper->owner->email)
            && $mail->notice->outcome === 'accepted');
    }

    public function test_failed_mail_can_be_requeued_without_creating_another_outbox_record(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);
        $outbox = DecisionNotification::query()->sole();
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Simulated mailer failure'));
        (new SendDecisionNotification($outbox->id))->handle();
        $this->assertSame('failed', $outbox->fresh()->status);
        app(RetryDecisionNotification::class)->handle($actor, $outbox->id);
        $this->assertSame('queued', $outbox->fresh()->status);
        $this->assertDatabaseCount('decision_notifications', 1);
    }

    public function test_database_rejects_duplicate_notification_event(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);
        $notice = DecisionNotification::query()->sole();

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('decision_notifications')->insert([
            'id' => (string) Str::uuid7(), 'paper_id' => $paper->id,
            'paper_decision_event_id' => $notice->paper_decision_event_id,
            'decision_version' => 1, 'event_type' => 'published',
            'recipient_email' => $notice->recipient_email,
            'recipient_name' => $notice->recipient_name,
            'paper_reference' => $notice->paper_reference,
            'paper_title' => $notice->paper_title,
            'outcome' => 'accepted', 'status' => 'queued',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_livewire_stages_bulk_override_but_only_confirms_with_owner_action(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed('reject');
        $component = Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->assertSee($paper->reference)
            ->call('toggleSelected', $paper->public_id)
            ->assertSet('selectedIds', [$paper->public_id])
            ->call('openOverride', $paper->public_id)
            ->set('overrideTarget', 'accepted')
            ->set('overrideReason', 'Authority verified substantive supporting research evidence.')
            ->call('saveOverride')
            ->assertHasNoErrors()
            ->call('askPublish', false)
            ->assertSet('publishModalOpen', true)
            ->assertSet('preview.total', 1)
            ->assertSet('preview.overridden', 1);

        $this->assertDatabaseCount('paper_decisions', 0);
        $component->call('confirmPublish')->assertHasNoErrors()
            ->assertSet('publishModalOpen', false)->assertSee('Batch completed');
        $this->assertSame(PaperStatus::Accepted, $paper->fresh()->status);
    }

    public function test_cancel_publish_does_not_change_author_state(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->call('askPublish', true)
            ->assertSet('publishModalOpen', true)
            ->call('cancelPublish')
            ->assertSet('publishModalOpen', false);
        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
        $this->assertDatabaseCount('paper_decisions', 0);
    }

    public function test_selected_papers_remain_selected_across_pagination(): void
    {
        $authority = $this->authority();
        $papers = [];
        for ($i = 0; $i < 11; $i++) {
            $papers[] = $this->reviewed();
        }

        $component = Livewire::actingAs($authority)->test(AcademicDecisions::class)
            ->call('toggleSelected', $papers[0]->public_id)
            ->call('nextPage')
            ->call('toggleSelected', $papers[10]->public_id)
            ->assertSet('selectedIds', [$papers[0]->public_id, $papers[10]->public_id])
            ->call('askPublish', false)
            ->assertSet('preview.total', 2)
            ->call('confirmPublish')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('paper_decisions', 2);
        $this->assertSame(9, app(ReadyAcademicDecisions::class)->query()->count());
    }

    public function test_all_ready_publishes_only_eligible_final_reviews(): void
    {
        $actor = $this->authority();
        $first = $this->reviewed('accept');
        $second = $this->reviewed('reject');
        $unreviewed = $this->paper();

        Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->call('askPublish', true)->assertSet('preview.total', 2)
            ->set('allReadyConfirmation', 'PUBLISH ALL READY')
            ->call('confirmPublish')->assertHasNoErrors();

        $this->assertSame(PaperStatus::Accepted, $first->fresh()->status);
        $this->assertSame(PaperStatus::Rejected, $second->fresh()->status);
        $this->assertSame(PaperStatus::Submitted, $unreviewed->fresh()->status);
        $this->assertDatabaseCount('decision_notifications', 2);
    }

    public function test_reviewer_can_still_read_own_submitted_review_after_published_decision(): void
    {
        $authority = $this->authority();
        $paper = $this->reviewed();
        $reviewer = $paper->reviews()->firstOrFail()->reviewer;

        app(PublishAcademicDecisions::class)->handle($authority, [$paper->public_id]);

        $this->actingAs($reviewer)
            ->get(route('reviewer.papers.show', $paper->public_id))
            ->assertOk()->assertSee('Review Submitted');

        $this->actingAs($reviewer)
            ->get(route('reviewer.workspace', ['tab' => 'mine']))
            ->assertOk()->assertSee($paper->reference);
    }

    public function test_correction_rejects_non_authority_even_with_known_paper_public_id(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);
        $unassigned = User::factory()->create(['role' => UserRole::Admin]);

        $this->expectException(AuthorizationException::class);
        app(CorrectAcademicDecision::class)->handle($unassigned, $paper->public_id, 'rejected',
            'Unauthorized actor should never be able to change published outcome.', 1);
    }

    public function test_recovery_command_requeues_stale_processing_outbox_without_changing_decision(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);

        $notice = DecisionNotification::query()->sole();
        $notice->status = 'processing';
        $notice->last_attempt_at = now()->subMinutes(15);
        $notice->save();

        $this->artisan('decisions:recover-notifications')->assertExitCode(0);
        $this->assertSame('queued', $notice->fresh()->status);
        $this->assertSame(PaperStatus::Accepted, $paper->fresh()->status);
        $this->assertDatabaseCount('decision_notifications', 1);
    }

    public function test_awaiting_tab_shows_unreviewed_paper_but_published_tab_only_shows_final_decisions(): void
    {
        $actor = $this->authority();
        $ready = $this->reviewed();
        $awaiting = $this->paper();

        Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->call('selectTab', 'awaiting')
            ->assertSee($awaiting->reference)
            ->assertDontSee($ready->reference)
            ->call('selectTab', 'published')
            ->assertSee('No published papers found');

        app(PublishAcademicDecisions::class)->handle($actor, [$ready->public_id]);

        Livewire::actingAs($actor)->test(AcademicDecisions::class, ['tab' => 'published'])
            ->assertSee($ready->reference)
            ->assertSee('Correct')
            ->assertSee('Queued')
            ->assertDontSee($awaiting->reference);
    }

    public function test_livewire_correction_modal_requires_reason_and_records_second_version(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();
        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);

        Livewire::actingAs($actor)->test(AcademicDecisions::class, ['tab' => 'published'])
            ->call('openCorrection', $paper->public_id)
            ->assertSet('correctionModalOpen', true)
            ->assertSet('correctionVersion', 1)
            ->set('correctionTarget', 'rejected')
            ->set('correctionReason', 'short')
            ->call('confirmCorrection')
            ->assertHasErrors('correction')
            ->set('correctionReason', 'Academic correction based on an authoritative follow-up check.')
            ->call('confirmCorrection')
            ->assertHasNoErrors()
            ->assertSet('correctionModalOpen', false);

        $this->assertSame(PaperStatus::Rejected, $paper->fresh()->status);
        $this->assertSame(2, $paper->fresh()->decision->version);
        $this->assertDatabaseCount('decision_notifications', 2);
    }

    public function test_header_checkbox_selects_only_current_page_and_can_deselect_it(): void
    {
        $actor = $this->authority();
        $papers = [];
        for ($i = 0; $i < 13; $i++) {
            $papers[] = $this->reviewed();
        }

        $component = Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->assertSee('Select all papers on this page')
            ->assertSee('Publish Selected')
            ->call('toggleCurrentPage')
            ->assertCount('selectedIds', 10)
            ->assertSee('10 selected');

        $firstPage = $component->get('selectedIds');

        $component->call('nextPage')
            ->call('toggleCurrentPage')
            ->assertCount('selectedIds', 13)
            ->call('toggleCurrentPage')
            ->assertSet('selectedIds', $firstPage)
            ->call('previousPage')
            ->call('toggleCurrentPage')
            ->assertSet('selectedIds', [])
            ->assertHasNoErrors();

        $this->assertDatabaseCount('paper_decisions', 0);
    }

    public function test_select_visible_page_respects_search_and_filter_and_clear_selection(): void
    {
        $actor = $this->authority();
        $first = $this->reviewed();
        $second = $this->reviewed();
        $second->title = 'Unique bulk checkbox search target';
        $second->save();

        Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->set('search', 'Unique bulk checkbox search target')
            ->call('toggleCurrentPage')
            ->assertSet('selectedIds', [$second->public_id])
            ->call('clearSelection')
            ->assertSet('selectedIds', [])
            ->assertHasNoErrors();

        $this->assertSame(PaperStatus::Submitted, $first->fresh()->status);
    }

    public function test_publish_all_requires_explicit_typed_confirmation(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed();

        $component = Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->call('askPublish', true)
            ->assertSet('publishingAllReady', true)
            ->assertSee('PUBLISH ALL READY')
            ->call('confirmPublish')
            ->assertHasErrors('publish');

        $this->assertDatabaseCount('paper_decisions', 0);

        $component->set('allReadyConfirmation', 'PUBLISH ALL READY')
            ->call('confirmPublish')
            ->assertHasNoErrors();
        $this->assertSame(PaperStatus::Accepted, $paper->fresh()->status);
    }

    public function test_publish_selected_does_not_publish_other_eligible_papers(): void
    {
        $actor = $this->authority();
        $selected = $this->reviewed('accept');
        $otherReady = $this->reviewed('reject');

        Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->call('toggleSelected', $selected->public_id)
            ->assertSet('selectedIds', [$selected->public_id])
            ->call('askPublish', false)
            ->assertSet('preview.total', 1)
            ->call('confirmPublish')
            ->assertHasNoErrors();

        $this->assertSame(PaperStatus::Accepted, $selected->fresh()->status);
        $this->assertSame(PaperStatus::Submitted, $otherReady->fresh()->status);
        $this->assertDatabaseCount('paper_decisions', 1);
        $this->assertDatabaseCount('decision_notifications', 1);
    }

    public function test_reviewer_comments_are_automatically_shared_without_per_paper_approval(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed('reject', 'Specific author-facing General Comments for the rejected paper.');

        $this->actingAs($paper->owner)->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertDontSee('Specific author-facing General Comments');

        $component = Livewire::actingAs($actor)->test(AcademicDecisions::class)
            ->assertSee('Komentar otomatis ditampilkan kepada author')
            ->assertDontSee('Tampilkan komentar kepada author')
            ->call('toggleSelected', $paper->public_id)
            ->call('askPublish', false)
            ->assertSet('preview.comments', 1)
            ->assertSee('Reviewer comments included');

        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
        $this->assertDatabaseCount('paper_decisions', 0);

        $component->call('confirmPublish')->assertHasNoErrors();
        $this->assertSame('Specific author-facing General Comments for the rejected paper.', $paper->fresh()->decision->public_comments);
        $this->assertSame('Specific author-facing General Comments for the rejected paper.', $paper->decisionNotifications()->firstOrFail()->public_comments);
        $this->assertSame(PaperStatus::Rejected, $paper->fresh()->status);

        $this->actingAs($paper->owner)->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Reviewer General Comments')
            ->assertSee('Specific author-facing General Comments for the rejected paper.');
    }

    public function test_corrected_decision_keeps_automatic_reviewer_comments_and_new_outbox_snapshot(): void
    {
        $actor = $this->authority();
        $paper = $this->reviewed('reject', 'Author should strengthen the evidence and discussion.');

        app(PublishAcademicDecisions::class)->handle($actor, [$paper->public_id]);
        $corrected = app(CorrectAcademicDecision::class)->handle(
            $actor,
            $paper->public_id,
            'accepted',
            'Academic board verified the revised evidence during subsequent review.',
            1,
        );

        $this->assertSame(2, $corrected->version);
        $this->assertSame('Author should strengthen the evidence and discussion.', $corrected->public_comments);
        $this->assertSame('Author should strengthen the evidence and discussion.', $paper->decisionNotifications()->firstOrFail()->public_comments);
        $this->assertSame('Author should strengthen the evidence and discussion.', $corrected->events()->where('version', 2)->firstOrFail()->public_comments);
        $this->actingAs($paper->owner)->get(route('papers.show', $paper->public_id))
            ->assertSee('Author should strengthen the evidence and discussion.')
            ->assertDontSee('Academic board verified the revised evidence during subsequent review.');
    }
}
