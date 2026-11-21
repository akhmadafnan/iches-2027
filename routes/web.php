<?php

use App\Livewire\Admin\ManageConferenceTracks;
use App\Livewire\Admin\ManageEventSettings;
use App\Livewire\Admin\ManagePrograms;
use App\Livewire\Admin\ManageUsers;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

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

    Route::view('reviewer/workspace', 'workspaces.reviewer')
        ->middleware('can:review-papers')
        ->name('reviewer.workspace');

    Route::view('committee/workspace', 'workspaces.committee')
        ->middleware('can:scan-services')
        ->name('committee.workspace');
});

require __DIR__.'/settings.php';
