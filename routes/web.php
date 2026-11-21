<?php

use App\Http\Controllers\PaperAbstractDownloadController;
use App\Http\Controllers\ReviewerAbstractDownloadController;
use App\Livewire\Admin\ManageConferenceTracks;
use App\Livewire\Admin\ManageEventSettings;
use App\Livewire\Admin\ManagePrograms;
use App\Livewire\Admin\ManageUsers;
use App\Livewire\Conference\MyPapers;
use App\Livewire\Conference\PaperDetails;
use App\Livewire\Conference\PaperEditor;
use App\Livewire\Dashboard;
use App\Livewire\Reviewer\AvailablePapers;
use App\Livewire\Reviewer\PaperPreview;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

    Route::livewire('my/submissions', MyPapers::class)->name('papers.index');
    Route::livewire('my/submissions/create', PaperEditor::class)->name('papers.create');
    Route::livewire('my/submissions/{paper:public_id}', PaperDetails::class)->name('papers.show');
    Route::livewire('my/submissions/{paper:public_id}/edit', PaperEditor::class)->name('papers.edit');
    Route::get('my/submissions/{paper:public_id}/abstract-pdf', PaperAbstractDownloadController::class)->name('papers.abstract.download');

    Route::livewire('admin/programs', ManagePrograms::class)
        ->middleware('can:manage-programs')
        ->name('admin.programs');

    Route::livewire('admin/conference-tracks', ManageConferenceTracks::class)
        ->middleware('can:manage-conference-tracks')
        ->name('admin.tracks');

    Route::livewire('admin/event-settings', ManageEventSettings::class)
        ->middleware('can:manage-event-settings')
        ->name('admin.event-settings');

    Route::livewire('admin/users', ManageUsers::class)
        ->middleware('can:manage-users')
        ->name('admin.users');

    Route::livewire('reviewer/workspace', AvailablePapers::class)
        ->middleware('can:review-papers')
        ->name('reviewer.workspace');
    Route::livewire('reviewer/papers/{paper:public_id}', PaperPreview::class)
        ->middleware('can:review-papers')
        ->name('reviewer.papers.show');
    Route::get('reviewer/papers/{paper:public_id}/abstract-pdf', ReviewerAbstractDownloadController::class)
        ->middleware('can:review-papers')
        ->name('reviewer.papers.pdf');

    Route::view('committee/workspace', 'workspaces.committee')
        ->middleware('can:scan-services')
        ->name('committee.workspace');
});

require __DIR__.'/settings.php';
