<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QbittorrentController;

Route::get('/qbittorrent', [QbittorrentController::class, 'downloadInfo'])->name('qbittorrent.downloadInfo');
Route::post('/qbittorrent/add', [QbittorrentController::class, 'add'])->name('qbittorrent.add');
Route::get('/qbittorrent/refresh', [QbittorrentController::class, 'refresh'])->name('qbittorrent.refresh');