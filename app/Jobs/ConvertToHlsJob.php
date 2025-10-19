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

    public $timeout = 7200; // 2 jam
    public $tries = 1;      // hanya 1x percobaan
    public $failOnTimeout = false; // biar gak dilempar exception otomatis

    protected $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function handle()
    {
        $inputPath = $this->content->full_path;
        $outputFolder = $this->content->name;

        if (!file_exists($inputPath)) {
            Log::error("❌ File tidak ditemukan: {$inputPath}");
            return;
        }

        $outputPath = storage_path("app/public/hls/{$outputFolder}");
        if (!file_exists($outputPath)) mkdir($outputPath, 0777, true);

        $cmd = sprintf(
            'ffmpeg -y -i "%s" -profile:v baseline -level 3.0 -start_number 0 -hls_time 10 -hls_list_size 0 -f hls "%s/index.m3u8"',
            $inputPath,
            $outputPath
        );

        $descriptorspec = [
            0 => ["pipe", "r"],   // stdin
            1 => ["pipe", "w"],   // stdout
            2 => ["pipe", "w"],   // stderr
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fclose($pipes[0]);
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            $start = time();
            $maxDuration = 7200; // 2 jam

            while (true) {
                $status = proc_get_status($process);
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);

                if ($stdout) Log::debug("FFmpeg out: " . trim($stdout));
                if ($stderr) Log::debug("FFmpeg err: " . trim($stderr));

                if (!$status['running']) break;

                if (time() - $start > $maxDuration) {
                    Log::warning("⚠️ Konversi terlalu lama, dihentikan paksa.");
                    proc_terminate($process);
                    break;
                }

                sleep(5);
            }

            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            if ($exitCode !== 0) {
                Log::error("❌ Konversi HLS gagal untuk: {$inputPath}");
                return;
            }
        }

        $this->content->update([
            'status' => 'converted',
            'file_path' => str_replace(storage_path('app/public/'), 'storage/', "{$outputPath}/index.m3u8"),
        ]);

        Log::info("✅ Konversi HLS berhasil untuk: {$inputPath}");
    }

}
