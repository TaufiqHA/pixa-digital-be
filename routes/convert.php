<?php

use App\Http\Controllers\ConvertController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QbittorrentController;

Route::post('/convert', [ConvertController::class,'convert'])->name('convert');
