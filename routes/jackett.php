<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JackettController;
use GuzzleHttp\Middleware;

Route::middleware('auth')->group(function() {
  Route::get('/jackett', [JackettController::class, 'index'])->name('jackett.index');
  Route::post('/jackett/search', [JackettController::class, 'search'])->name('jackett.search');
});
