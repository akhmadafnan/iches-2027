<?php

use App\Livewire\Admin\ManageUsers;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

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
