<?php

namespace Tests\Feature\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Actions\Conference\SubmitPaper;
use App\Enums\PaperStatus;
use App\Livewire\Conference\MyPapers;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use App\Services\Conference\PaperSubmissionReadiness;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PaperSubmitWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(EventMasterSeeder::class);
        $this->seed(ConferenceTrackSeeder::class);
    }

    private function track(): ConferenceTrack
    {
        return ConferenceTrack::query()->where('code', 'TRK-01')->firstOrFail();
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'original-abstract.pdf',
            "%PDF-1.4\n1 0 obj\n<</Type /Catalog>>\nendobj\ntrailer\n<</Root 1 0 R>>\n%%EOF\n",
        );
    }

    private function makePaper(User $owner, bool $complete = true): Paper
    {
        return app(SavePaperDraft::class)->handle(
            $owner,
            null,
            ['title' => 'Cultural Memory and Education in Asian Pesantren', 'conference_track_id' => $this->track()->id],
            [['name' => $owner->name, 'email' => $owner->email, 'institution' => 'University of Example', 'affiliation_source' => 'manual']],
            0,
            $complete ? ['abstract_text' => 'This paper discusses the history of education and pesantren culture.', 'keywords' => ['Education', 'Pesantren']] : [],
            $complete ? $this->pdf() : null,
        );
    }

    public function test_incomplete_draft_shows_only_edit_and_cannot_be_submitted(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner, complete: false);

        $this->assertFalse(app(PaperSubmissionReadiness::class)->ready($paper));
        Livewire::actingAs($owner)->test(MyPapers::class)
            ->assertSee('Edit draft '.$paper->reference)
            ->assertDontSee('Submit paper '.$paper->reference)
            ->call('askSubmit', $paper->public_id)
            ->assertSet('submitModalOpen', false)
            ->assertHasErrors('paper');

        try {
            app(SubmitPaper::class)->handle($owner, $paper->public_id);
            $this->fail('Incomplete paper must not pass direct server submission.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('paper', $exception->errors());
        }

        $paper->refresh();
        $this->assertSame(PaperStatus::Draft, $paper->status);
        $this->assertNull($paper->submitted_at);
        $this->assertSame(0, $paper->submission_version);
        $this->assertSame(0, $paper->statusHistories()->count());
    }

    public function test_complete_draft_reveals_submit_icon_and_can_cancel_confirmation(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner);

        Livewire::actingAs($owner)->test(MyPapers::class)
            ->assertSee('Submit paper '.$paper->reference)
            ->call('askSubmit', $paper->public_id)
            ->assertHasNoErrors()
            ->assertSet('submitModalOpen', true)
            ->assertSee('Submit paper?')
            ->call('cancelSubmit')
            ->assertSet('submitModalOpen', false)
            ->assertSet('confirmingPublicId', null);

        $this->assertSame(PaperStatus::Draft, $paper->fresh()->status);
    }

    public function test_confirmation_submits_once_and_preserves_paper_identity_and_authors(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner);
        $reference = $paper->reference;
        $id = $paper->id;
        $publicId = $paper->public_id;
        $pdfPath = $paper->abstract_pdf_path;
        $authorId = $paper->authors()->sole()->id;

        Livewire::actingAs($owner)->test(MyPapers::class)
            ->call('askSubmit', $paper->public_id)
            ->call('confirmSubmit')
            ->assertHasNoErrors()
            ->assertSet('submitModalOpen', false)
            ->assertSee('View submission '.$reference)
            ->assertDontSee('Edit draft '.$reference)
            ->assertDontSee('Submit paper '.$reference);

        $paper->refresh();
        $this->assertSame($id, $paper->id);
        $this->assertSame($reference, $paper->reference);
        $this->assertSame($publicId, $paper->public_id);
        $this->assertSame(PaperStatus::Submitted, $paper->status);
        $this->assertNotNull($paper->submitted_at);
        $this->assertSame(1, $paper->submission_version);
        $this->assertSame($pdfPath, $paper->abstract_pdf_path);
        $this->assertSame($authorId, $paper->authors()->sole()->id);
        Storage::disk('local')->assertExists($pdfPath);

        $history = $paper->statusHistories()->sole();
        $this->assertSame('submitted', $history->event);
        $this->assertSame('draft', $history->from_status);
        $this->assertSame('submitted', $history->to_status);
        $this->assertSame(1, $history->submission_version);
        $this->assertSame($owner->id, $history->actor_id);
    }

    public function test_submitted_paper_cannot_be_submitted_twice_or_edited(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner);
        app(SubmitPaper::class)->handle($owner, $paper->public_id);

        try {
            app(SubmitPaper::class)->handle($owner, $paper->public_id);
            $this->fail('Duplicate submission must fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('paper', $exception->errors());
        }

        $this->assertSame(1, $paper->statusHistories()->count());
        $this->assertSame(1, $paper->fresh()->submission_version);
        $this->actingAs($owner)->get(route('papers.edit', $paper->public_id))->assertForbidden();
    }

    public function test_other_user_cannot_open_submit_dialog_submit_or_view_paper(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $paper = $this->makePaper($owner);

        Livewire::actingAs($other)->test(MyPapers::class)
            ->call('askSubmit', $paper->public_id)
            ->assertNotFound();

        $this->expectException(AuthorizationException::class);
        app(SubmitPaper::class)->handle($other, $paper->public_id);
    }

    public function test_submitted_record_is_owner_only_with_read_only_details_and_private_download(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $paper = $this->makePaper($owner);
        app(SubmitPaper::class)->handle($owner, $paper->public_id);

        $this->actingAs($owner)->get(route('papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee('Status timeline')
            ->assertSee('Paper submitted')
            ->assertSee('Version 1')
            ->assertSee('Download PDF')
            ->assertDontSee('Confirm submission');
        $this->get(route('papers.abstract.download', $paper->public_id))->assertDownload();
        $this->actingAs($other)->get(route('papers.show', $paper->public_id))->assertForbidden();
        $this->get(route('papers.abstract.download', $paper->public_id))->assertForbidden();
    }

    public function test_rechecks_pdf_and_metadata_after_modal_was_opened(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner);

        $component = Livewire::actingAs($owner)->test(MyPapers::class)
            ->call('askSubmit', $paper->public_id)
            ->assertSet('submitModalOpen', true);

        Storage::disk('local')->delete($paper->abstract_pdf_path);

        $component->call('confirmSubmit')
            ->assertHasErrors('paper');

        $this->assertSame(PaperStatus::Draft, $paper->fresh()->status);
        $this->assertSame(0, $paper->statusHistories()->count());
    }

    public function test_invalid_author_or_disabled_track_blocks_submit(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner);

        $paper->authors()->update(['is_corresponding' => false]);
        $this->assertFalse(app(PaperSubmissionReadiness::class)->ready($paper->fresh()));

        $paper->authors()->update(['is_corresponding' => true]);
        $this->track()->update(['is_active' => false]);
        $this->assertFalse(app(PaperSubmissionReadiness::class)->ready($paper->fresh()));

        $this->assertSame(PaperStatus::Draft, $paper->fresh()->status);
    }

    public function test_forged_modal_state_cannot_submit_someone_elses_paper(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $paper = $this->makePaper($owner);

        Livewire::actingAs($attacker)->test(MyPapers::class)
            ->set('submitModalOpen', true)
            ->set('confirmingPublicId', $paper->public_id)
            ->call('confirmSubmit')
            ->assertForbidden();

        $this->assertSame(PaperStatus::Draft, $paper->fresh()->status);
        $this->assertSame(0, $paper->statusHistories()->count());
    }

    public function test_malformed_saved_keywords_do_not_make_paper_ready(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner);
        $paper->keywords = ['Education', 'education'];
        $paper->save();

        $this->assertFalse(app(PaperSubmissionReadiness::class)->ready($paper->fresh()));

        Livewire::actingAs($owner)->test(MyPapers::class)
            ->assertDontSee('Submit paper '.$paper->reference);
        $this->assertSame(PaperStatus::Draft, $paper->fresh()->status);
    }

    public function test_draft_may_be_opened_read_only_without_changing_status(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makePaper($owner, complete: false);

        $this->actingAs($owner)->get(route('papers.show', $paper->public_id))
            ->assertOk()->assertSee('Draft created')->assertSee('Edit draft');
        $this->assertSame(PaperStatus::Draft, $paper->fresh()->status);
    }
}
