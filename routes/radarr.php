<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RadarrController;

Route::post('/radarr/search', [RadarrController::class, 'search'])->name('radarr.search');
Route::post('/radarr/addMovie', [RadarrController::class, 'addMovie'])->name('radarr.addMovie');