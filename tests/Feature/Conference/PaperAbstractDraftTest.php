<?php

namespace Tests\Feature\Conference;

use App\Actions\Conference\SavePaperDraft;
use App\Enums\PaperStatus;
use App\Livewire\Conference\PaperEditor;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PaperAbstractDraftTest extends TestCase
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

    private function makeDraft(User $owner): Paper
    {
        return app(SavePaperDraft::class)->handle(
            $owner,
            null,
            ['title' => 'Education and Cultural Collaboration in Asia', 'conference_track_id' => $this->track()->id],
            [['name' => $owner->name, 'email' => $owner->email, 'institution' => 'University of Example', 'affiliation_source' => 'manual']],
            0,
        );
    }

    private function pdf(string $filename = 'submission.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $filename,
            "%PDF-1.4\n1 0 obj\n<</Type /Catalog>>\nendobj\ntrailer\n<</Root 1 0 R>>\n%%EOF\n",
        );
    }

    public function test_existing_draft_has_additive_nullable_academic_fields_and_keeps_identity(): void
    {
        $user = User::factory()->create();
        $paper = $this->makeDraft($user);

        $this->assertNull($paper->abstract_text);
        $this->assertNull($paper->keywords);
        $this->assertNull($paper->abstract_pdf_path);
        $this->assertSame(PaperStatus::Draft, $paper->status);

        Livewire::actingAs($user)->test(PaperEditor::class, ['paper' => $paper])
            ->assertSet('abstractText', '')
            ->assertSet('keywords', [])
            ->assertSee('Abstract &amp; keywords', escape: false)
            ->assertSee('Abstract PDF')
            ->assertSee('Add keyword')
            ->assertSee('Save draft');
    }

    public function test_keywords_are_added_one_at_a_time_trimmed_and_de_duplicated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->set('keywordInput', '  Islamic    Education  ')
            ->call('addKeyword')
            ->assertSet('keywords', ['Islamic Education'])
            ->assertSet('keywordInput', '')
            ->set('keywordInput', 'ISLAMIC education')
            ->call('addKeyword')
            ->assertHasErrors('keywordInput')
            ->assertCount('keywords', 1)
            ->set('keywordInput', 'Pesantren')
            ->call('addKeyword')
            ->assertSet('keywords', ['Islamic Education', 'Pesantren'])
            ->call('removeKeyword', 0)
            ->assertSet('keywords', ['Pesantren']);
    }

    public function test_keywords_limit_and_minimum_length_are_checked(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PaperEditor::class)
            ->set('keywordInput', 'A')
            ->call('addKeyword')
            ->assertHasErrors('keywordInput')
            ->set('keywords', array_map(static fn (int $number): string => "Term {$number}", range(1, 15)))
            ->set('keywordInput', 'Extra Keyword')
            ->call('addKeyword')
            ->assertHasErrors('keywordInput')
            ->assertCount('keywords', 15);
    }

    public function test_abstract_text_and_keywords_persist_in_same_draft_without_submitting(): void
    {
        $user = User::factory()->create();
        $paper = $this->makeDraft($user);
        $reference = $paper->reference;
        $publicId = $paper->public_id;

        Livewire::actingAs($user)->test(PaperEditor::class, ['paper' => $paper])
            ->set('abstractText', '  This study examines university partnerships in inclusive Islamic education.  ')
            ->set('keywordInput', 'Islamic Education')
            ->call('addKeyword')
            ->set('keywordInput', 'Community Partnership')
            ->call('addKeyword')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('papers.index'));

        $paper->refresh();
        $this->assertSame($reference, $paper->reference);
        $this->assertSame($publicId, $paper->public_id);
        $this->assertSame(PaperStatus::Draft, $paper->status);
        $this->assertSame('This study examines university partnerships in inclusive Islamic education.', $paper->abstract_text);
        $this->assertSame(['Islamic Education', 'Community Partnership'], $paper->keywords);
        $this->assertCount(1, $paper->authors);
    }

    public function test_last_uncommitted_keyword_is_included_when_saving_draft(): void
    {
        $user = User::factory()->create();
        $paper = $this->makeDraft($user);

        Livewire::actingAs($user)->test(PaperEditor::class, ['paper' => $paper])
            ->set('keywordInput', 'Intercultural Dialogue')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['Intercultural Dialogue'], $paper->fresh()->keywords);
    }

    public function test_server_rejects_duplicate_keyword_bypass(): void
    {
        $user = User::factory()->create();
        $paper = $this->makeDraft($user);

        $this->expectException(ValidationException::class);

        app(SavePaperDraft::class)->handle(
            $user,
            $paper,
            ['title' => $paper->title, 'conference_track_id' => $this->track()->id],
            [['name' => $user->name, 'email' => $user->email, 'institution' => 'University of Example', 'affiliation_source' => 'manual']],
            0,
            ['keywords' => ['Islamic Education', 'islamic education']],
        );
    }

    public function test_valid_pdf_is_private_downloadable_by_owner_and_metadata_persists(): void
    {
        $user = User::factory()->create();
        $paper = $this->makeDraft($user);

        Livewire::actingAs($user)->test(PaperEditor::class, ['paper' => $paper])
            ->set('abstractPdfUpload', $this->pdf('original-abstract.pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $paper->refresh();
        $this->assertNotNull($paper->abstract_pdf_path);
        $this->assertSame('original-abstract.pdf', $paper->abstract_pdf_original_name);
        $this->assertGreaterThan(0, $paper->abstract_pdf_size);
        $this->assertNotNull($paper->abstract_pdf_uploaded_at);
        $this->assertSame(PaperStatus::Draft, $paper->status);
        Storage::disk('local')->assertExists($paper->abstract_pdf_path);
        Storage::disk('public')->assertMissing($paper->abstract_pdf_path);

        $this->actingAs($user)
            ->get(route('papers.abstract.download', $paper->public_id))
            ->assertOk()
            ->assertDownload("ICHES2027-Abstract-{$paper->reference}.pdf")
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_guest_and_another_user_cannot_download_private_abstract(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $paper = $this->makeDraft($owner);

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('abstractPdfUpload', $this->pdf())
            ->call('save')
            ->assertHasNoErrors();

        $this->app['auth']->guard()->logout();
        $this->get(route('papers.abstract.download', $paper->public_id))->assertRedirect(route('login'));
        $this->actingAs($other)->get(route('papers.abstract.download', $paper->public_id))->assertForbidden();
        $this->actingAs($other)->get(route('papers.edit', $paper->public_id))->assertNotFound();
        Storage::disk('local')->assertExists($paper->fresh()->abstract_pdf_path);
    }

    public function test_nonexistent_pdf_returns_not_found_to_owner(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);

        $this->actingAs($owner)->get(route('papers.abstract.download', $paper->public_id))->assertNotFound();
    }

    public function test_invalid_pdf_content_cannot_replace_existing_valid_file(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('abstractPdfUpload', $this->pdf())
            ->call('save')
            ->assertHasNoErrors();

        $original = $paper->fresh()->abstract_pdf_path;
        $invalid = UploadedFile::fake()->createWithContent('renamed.pdf', 'plain text not a PDF');

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper->fresh()])
            ->set('abstractPdfUpload', $invalid)
            ->call('save')
            ->assertHasErrors('abstractPdfUpload');

        $this->assertSame($original, $paper->fresh()->abstract_pdf_path);
        Storage::disk('local')->assertExists($original);
    }

    public function test_pdf_replacement_keeps_same_paper_identity_and_deletes_old_blob(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);
        $reference = $paper->reference;
        $publicId = $paper->public_id;

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('abstractPdfUpload', $this->pdf('first.pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $firstPath = $paper->fresh()->abstract_pdf_path;

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper->fresh()])
            ->set('abstractPdfUpload', $this->pdf('second.pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $paper->refresh();
        $this->assertNotSame($firstPath, $paper->abstract_pdf_path);
        $this->assertSame('second.pdf', $paper->abstract_pdf_original_name);
        $this->assertSame($reference, $paper->reference);
        $this->assertSame($publicId, $paper->public_id);
        $this->assertSame(1, $paper->authors()->count());
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($paper->abstract_pdf_path);
    }

    public function test_remove_and_undo_removal_are_staged_until_save(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('abstractPdfUpload', $this->pdf())
            ->call('save')
            ->assertHasNoErrors();

        $path = $paper->fresh()->abstract_pdf_path;

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper->fresh()])
            ->call('markPdfForRemoval')
            ->assertSet('removeAbstractPdf', true)
            ->call('undoPdfRemoval')
            ->assertSet('removeAbstractPdf', false);

        Storage::disk('local')->assertExists($path);
        $this->assertSame($path, $paper->fresh()->abstract_pdf_path);

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper->fresh()])
            ->call('markPdfForRemoval')
            ->call('save')
            ->assertHasNoErrors();

        $paper->refresh();
        $this->assertNull($paper->abstract_pdf_path);
        $this->assertNull($paper->abstract_pdf_original_name);
        $this->assertNull($paper->abstract_pdf_uploaded_at);
        $this->assertSame(PaperStatus::Draft, $paper->status);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_non_draft_owner_cannot_modify_abstract_or_file(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);
        $paper->status = PaperStatus::Submitted;
        $paper->save();

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])->assertForbidden();
        $this->actingAs($owner)->get(route('papers.edit', $paper->public_id))->assertForbidden();

        $this->assertNull($paper->fresh()->abstract_pdf_path);
    }

    public function test_new_draft_can_create_paper_and_store_pdf_in_one_save(): void
    {
        $owner = User::factory()->create();

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->set('title', 'Research Collaboration and Sustainable Education')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->call('useManualAffiliation')
            ->call('saveAuthor')
            ->set('abstractText', 'The program explores collaboration across universities.')
            ->set('keywordInput', 'Higher Education')
            ->call('addKeyword')
            ->set('abstractPdfUpload', $this->pdf('new-paper.pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $paper = Paper::query()->sole();
        $this->assertSame(PaperStatus::Draft, $paper->status);
        $this->assertSame(['Higher Education'], $paper->keywords);
        $this->assertSame(1, $paper->authors()->count());
        Storage::disk('local')->assertExists($paper->abstract_pdf_path);
    }

    public function test_pdf_extension_must_be_pdf_even_when_bytes_are_pdf(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('abstractPdfUpload', $this->pdf('renamed.txt'))
            ->call('save')
            ->assertHasErrors('abstractPdfUpload');

        $this->assertNull($paper->fresh()->abstract_pdf_path);
    }

    public function test_oversized_pdf_is_rejected_without_mutating_existing_paper(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);
        $originalTitle = $paper->title;

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('title', 'A New Version Which Must Not Be Saved')
            ->set('abstractPdfUpload', UploadedFile::fake()->create('too-large.pdf', 10241, 'application/pdf'))
            ->call('save')
            ->assertHasErrors('abstractPdfUpload');

        $this->assertSame($originalTitle, $paper->fresh()->title);
        $this->assertNull($paper->fresh()->abstract_pdf_path);
    }

    public function test_invalid_pending_keyword_does_not_accidentally_save_paper(): void
    {
        $owner = User::factory()->create();
        $paper = $this->makeDraft($owner);
        $initialTitle = $paper->title;

        Livewire::actingAs($owner)->test(PaperEditor::class, ['paper' => $paper])
            ->set('title', 'Changed Title Must Not Persist')
            ->set('keywordInput', 'Sustainable Education')
            ->call('addKeyword')
            ->set('keywordInput', 'SUSTAINABLE EDUCATION')
            ->call('save')
            ->assertHasErrors('keywordInput');

        $this->assertSame($initialTitle, $paper->fresh()->title);
        $this->assertNull($paper->fresh()->keywords);
    }

    public function test_draft_can_be_saved_without_abstract_keywords_or_pdf(): void
    {
        $owner = User::factory()->create();

        Livewire::actingAs($owner)->test(PaperEditor::class)
            ->set('title', 'Academic Exchange and Social Innovation')
            ->set('conferenceTrackId', (string) $this->track()->id)
            ->call('editAuthor', 0)
            ->call('useManualAffiliation')
            ->call('saveAuthor')
            ->call('save')
            ->assertHasNoErrors();

        $paper = Paper::query()->sole();
        $this->assertSame(PaperStatus::Draft, $paper->status);
        $this->assertNull($paper->abstract_text);
        $this->assertSame([], $paper->keywords);
        $this->assertNull($paper->abstract_pdf_path);
    }
}
