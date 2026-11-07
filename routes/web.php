<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VaultController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

Route::scopeBindings()->group(function (): void {
    Route::get('vaults/{vault}/cover', [VaultController::class, 'cover'])->name('vaults.cover');
    Route::resource('vaults', VaultController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('vaults.sections', SectionController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('vaults.sections.problems', ProblemController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('vaults.sections.problems.attempts', AttemptController::class)->only(['store', 'update', 'destroy']);
});
