<?php

use App\Livewire\Admin\ManageUsers;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

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
