<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class StreamController extends Controller
{
    public function stream($hash, $file = 'index.m3u8')
    {
        $path = storage_path("app/public/hls/{$hash}/{$file}");

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Deteksi MIME type (agar video player tahu ini HLS)
        $mime = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'm3u8' => 'application/vnd.apple.mpegurl',
            'ts'   => 'video/mp2t',
            default => 'application/octet-stream',
        };

        return Response::make(file_get_contents($path), 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'no-cache',
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
