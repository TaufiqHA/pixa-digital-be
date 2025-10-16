<?php

namespace App\Jobs;

use App\Http\Controllers\OmdbController;
use App\Models\Content;
use App\Models\Torrent;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ConvertToHlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    protected $torrent;

    public function __construct(Torrent $torrent)
    {
        $this->torrent = $torrent;
    }

    public function handle()
    {
        $inputPath = $this->torrent->download_path;
        $outputFolder = $this->torrent->hash;

        if (!file_exists($inputPath)) {
            Log::error("❌ File tidak ditemukan: {$inputPath}");
            return;
        }

        $outputPath = storage_path("app/public/hls/{$outputFolder}");

        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        // Jalankan ffmpeg command langsung (lebih stabil untuk HLS)
        $cmd = sprintf(
            'ffmpeg -i "%s" -profile:v baseline -level 3.0 -start_number 0 -hls_time 10 -hls_list_size 0 -f hls "%s/index.m3u8"',
            $inputPath,
            $outputPath
        );

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error("❌ Konversi HLS gagal untuk: {$inputPath}", $output);
            return;
        }

        // Update database status + path hasil
        $this->torrent->update([
            'status' => 'converted',
            'download_path' => str_replace(storage_path('app/public/'), 'storage/', "{$outputPath}/index.m3u8"),
        ]);

        $content = Content::where('id', $this->torrent->content_id)->first();

        $omdb = new OmdbController();

        $omdbData = $omdb->search($content->name);

        $content->update([
            'name' => $omdbData['Title'] ?? $content->name,
            'title' => $omdbData['Title'] ?? $content->title,
            'description' => $omdbData['Plot'] ?? $content->description,
            'release_year' => isset($omdbData['Year']) ? (int)$omdbData['Year'] : $content->release_year,
            'rating' => isset($omdbData['imdbRating']) && is_numeric($omdbData['imdbRating']) ? (float)$omdbData['imdbRating'] : $content->rating,
            'duration' => $omdbData['Runtime'] ?? $content->duration,
            'cover_image' => $omdbData['Poster'] ?? $content->cover_image,
            'status' => 'available'
        ]);

        Log::info("✅ Konversi HLS berhasil untuk: {$inputPath}");
    }
}
