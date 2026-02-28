<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

// Dashboard — aggregates & chart
Route::get('/dashboard', DashboardController::class)->name('dashboard');

// Entries — full CRUD
Route::resource('entries', EntryController::class)->except('show');

// Trash & restore routes — MUST come before the resource to avoid route conflicts
Route::prefix('entries')->name('entries.')->group(function () {
    Route::get('/trash', [EntryController::class, 'trash'])->name('trash');
    Route::patch('/{id}/restore', [EntryController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force', [EntryController::class, 'forceDelete'])->name('forceDelete');
    Route::delete('/trash/empty-all', [EntryController::class, 'emptyTrash'])->name('emptyTrash');
});
