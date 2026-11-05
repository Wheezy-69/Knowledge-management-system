<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('/vaults', 'vaults/Index')->name('vaults.index');
