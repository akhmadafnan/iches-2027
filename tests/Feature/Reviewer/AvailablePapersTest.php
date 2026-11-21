<?php

namespace Tests\Feature\Reviewer;

use App\Enums\PaperStatus;
use App\Enums\UserRole;
use App\Livewire\Reviewer\AvailablePapers;
use App\Models\ConferenceTrack;
use App\Models\Paper;
use App\Models\User;
use Database\Seeders\ConferenceTrackSeeder;
use Database\Seeders\EventMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AvailablePapersTest extends TestCase
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

    private function track(string $code = 'TRK-01'): ConferenceTrack
    {
        return ConferenceTrack::query()->where('code', $code)->firstOrFail();
    }

    private function paper(
        User $owner,
        PaperStatus $status = PaperStatus::Submitted,
        string $title = 'History of Education and Pesantren Traditions',
        string $trackCode = 'TRK-01',
    ): Paper {
        $ulid = (string) Str::ulid();
        $path = "paper-abstracts/{$owner->id}/{$ulid}/abstract.pdf";
        Storage::disk('local')->put($path, "%PDF-1.4\n% test document");

        $paper = Paper::query()->forceCreate([
            'owner_id' => $owner->id,
            'public_id' => $ulid,
            'conference_track_id' => $this->track($trackCode)->id,
            'title' => $title,
            'status' => $status,
            'abstract_text' => 'This study examines educational history in pesantren.',
            'keywords' => ['Education', 'History'],
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

    public function test_only_reviewer_role_can_open_reviewer_workspace(): void
    {
        $this->get(route('reviewer.workspace'))->assertRedirect(route('login'));
        foreach ([UserRole::User, UserRole::Committee, UserRole::Admin] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('reviewer.workspace'))
                ->assertForbidden();
        }

        $this->actingAs($this->reviewer())
            ->get(route('reviewer.workspace'))
            ->assertOk()
            ->assertSee('Available Papers')
            ->assertSee('My Reviews')
            ->assertSee('Coming in REV-002/003');
    }

    public function test_submitted_papers_appear_but_drafts_and_own_papers_do_not(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $submitted = $this->paper($author, title: 'Submitted Community Paper');
        $draft = $this->paper($author, PaperStatus::Draft, 'Private Draft Paper');
        $own = $this->paper($reviewer, title: 'My Own Submitted Paper');

        Livewire::actingAs($reviewer)->test(AvailablePapers::class)
            ->assertSee('Submitted Community Paper')
            ->assertSee($submitted->reference)
            ->assertDontSee($draft->reference)
            ->assertDontSee('Private Draft Paper')
            ->assertDontSee($own->reference)
            ->assertSee('Available Papers')
            ->assertDontSee('Claim paper')
            ->assertDontSee('Submit review');

        $this->assertSame(PaperStatus::Submitted, $submitted->fresh()->status);
    }

    public function test_title_reference_search_track_filter_and_reset_work(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $first = $this->paper($author, title: 'Social History Research', trackCode: 'TRK-01');
        $second = $this->paper($author, title: 'Health and Food Systems', trackCode: 'TRK-02');

        Livewire::actingAs($reviewer)->test(AvailablePapers::class)
            ->set('search', 'Social History')
            ->assertSee($first->reference)
            ->assertDontSee($second->reference)
            ->set('search', '')
            ->set('trackFilter', (string) $this->track('TRK-02')->id)
            ->assertDontSee($first->reference)
            ->assertSee($second->reference)
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('trackFilter', '')
            ->assertSee($first->reference)
            ->assertSee($second->reference);
    }

    public function test_read_only_preview_contains_abstract_keywords_and_private_pdf(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $paper = $this->paper($author);

        $this->actingAs($reviewer)
            ->get(route('reviewer.papers.show', $paper->public_id))
            ->assertOk()
            ->assertSee($paper->reference)
            ->assertSee($paper->title)
            ->assertSee('This study examines educational history')
            ->assertSee('Education')
            ->assertSee('Download PDF')
            ->assertSee('Claim paper')
            ->assertSee('Coming in REV-002')
            ->assertDontSee('Confirm submission');

        $this->get(route('reviewer.papers.pdf', $paper->public_id))
            ->assertOk()
            ->assertDownload("ICHES2027-Review-{$paper->reference}.pdf");
    }

    public function test_private_reviewer_pdf_and_preview_reject_guests_nonreviewers_drafts_and_own_papers(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $eligible = $this->paper($author);
        $draft = $this->paper($author, PaperStatus::Draft);
        $own = $this->paper($reviewer);

        auth()->logout();
        $this->get(route('reviewer.papers.show', $eligible->public_id))->assertRedirect(route('login'));
        $this->get(route('reviewer.papers.pdf', $eligible->public_id))->assertRedirect(route('login'));

        $this->actingAs($author)->get(route('reviewer.papers.show', $eligible->public_id))->assertForbidden();
        $this->get(route('reviewer.papers.pdf', $eligible->public_id))->assertForbidden();

        $this->actingAs($reviewer)->get(route('reviewer.papers.show', $draft->public_id))->assertNotFound();
        $this->get(route('reviewer.papers.pdf', $draft->public_id))->assertNotFound();
        $this->get(route('reviewer.papers.show', $own->public_id))->assertNotFound();
        $this->get(route('reviewer.papers.pdf', $own->public_id))->assertNotFound();
    }

    public function test_private_pdf_is_not_servable_when_file_missing_or_path_points_outside_namespace(): void
    {
        $reviewer = $this->reviewer();
        $author = User::factory()->create();
        $paper = $this->paper($author);

        Storage::disk('local')->delete($paper->abstract_pdf_path);
        $this->actingAs($reviewer)
            ->get(route('reviewer.papers.pdf', $paper->public_id))
            ->assertNotFound();

        $paper->abstract_pdf_path = 'paper-abstracts/other-owner/public-document.pdf';
        $paper->save();
        Storage::disk('local')->put($paper->abstract_pdf_path, '%PDF- fake');
        $this->get(route('reviewer.papers.pdf', $paper->public_id))->assertNotFound();
    }

    public function test_reviewer_index_has_empty_state_and_does_not_create_assignments_or_change_papers(): void
    {
        $reviewer = $this->reviewer();
        $this->actingAs($reviewer)->get(route('reviewer.workspace'))
            ->assertOk()
            ->assertSee('No papers available yet')
            ->assertSee('Coming in REV-002/003');

        $author = User::factory()->create();
        $paper = $this->paper($author);

        $this->get(route('reviewer.workspace'))->assertOk();
        $this->get(route('reviewer.papers.show', $paper->public_id))->assertOk();
        $this->get(route('reviewer.papers.pdf', $paper->public_id))->assertDownload();

        $this->assertSame(PaperStatus::Submitted, $paper->fresh()->status);
        $this->assertSame(1, $paper->fresh()->submission_version);
    }
}
