<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\RelatedProblemController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VaultController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);
Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
Route::get('backups/export', [BackupController::class, 'export'])->name('backups.export');
Route::post('backups', [BackupController::class, 'store'])->name('backups.store');

Route::scopeBindings()->group(function (): void {
    Route::get('vaults/{vault}/sections/{section}/problems/{problem}/compare', [ComparisonController::class, 'show'])->name('vaults.sections.problems.compare');
    Route::post('vaults/{vault}/sections/{section}/problems/{problem}/related', [RelatedProblemController::class, 'store'])->name('vaults.sections.problems.related.store');
    Route::delete('vaults/{vault}/sections/{section}/problems/{problem}/related/{related}', [RelatedProblemController::class, 'destroy'])->whereNumber('related')->name('vaults.sections.problems.related.destroy');
    Route::get('vaults/{vault}/cover', [VaultController::class, 'cover'])->name('vaults.cover');
    Route::resource('vaults', VaultController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('vaults.sections', SectionController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('vaults.sections.problems', ProblemController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('vaults.sections.problems.attempts', AttemptController::class)->only(['store', 'update', 'destroy']);
});
