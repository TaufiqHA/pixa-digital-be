<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SonarrController;

Route::get('/sonarr', [SonarrController::class, 'index'])->name('sonarr.index');
Route::get('/sonarr/search', [SonarrController::class, 'search'])->name('sonarr.search');
Route::post('/sonarr/add-tv-show', [SonarrController::class, 'addTvShow'])->name('sonarr.addTvShow');