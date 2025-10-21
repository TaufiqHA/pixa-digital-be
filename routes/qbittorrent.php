<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QbittorrentController;

Route::get('/qbittorrent', [QbittorrentController::class, 'downloadInfo'])->name('qbittorrent.downloadInfo');
Route::get('/qbittorrent/downloadedTorrent', [QbittorrentController::class, 'downloadedTorrent'])->name('qbittorrent.downloaded');
Route::post('/qbittorrent/add', [QbittorrentController::class, 'add'])->name('qbittorrent.add');
Route::get('/qbittorrent/refresh', [QbittorrentController::class, 'refresh'])->name('qbittorrent.refresh');
Route::post('qbittorrent/pause', [QbittorrentController::class, 'pause'])->name('qbittorrent.pause');
Route::post('/qbittorrent/resume', [QbittorrentController::class, 'resume'])->name('qbittorrent.resume');
Route::get('/qbittorrent/index', [QbittorrentController::class, 'index'])->name('qbittorrent.index');
// get file info
Route::get('/qbittorrent/fileinfo/{folderName}', [QbittorrentController::class, 'getMainFilePath'])->name('qbittorrent.fileinfo');