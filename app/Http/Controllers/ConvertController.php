<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Torrent;
use Illuminate\Http\Request;
use App\Jobs\ConvertToHlsJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ConvertController extends Controller
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $cookie;

    public function __construct()
    {
        $this->baseUrl = env('QBITTORRENT_URL', 'http://127.0.0.1:8080');
        $this->username = env('QBITTORRENT_USERNAME', 'admin');
        $this->password = env('QBITTORRENT_PASSWORD', 'malakaji');
    }
    public function convert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        $content = Content::where('id', $validated['id'])->first();

        $file_path = $this->getMainFilePath($content->file_path)[0];

        $content->update([
            'status' => 'converting',
            'full_path' => $file_path
        ]);

        $content->save();

        // Step 6: Dispatch job untuk konversi di background
        ConvertToHlsJob::dispatch($content);

        return back()->with('success', 'Proses konversi HLS telah dimulai di background.');
    }

    public function getMainFilePath($folderName)
    {
        // Path dasar ke folder torrent
        $folder = $folderName;

        if (!is_dir($folder)) {
            return []; // jika folder tidak ditemukan
        }

        $videoExtensions = ['mp4', 'mkv', 'avi', 'mov', 'wmv'];
        $files = scandir($folder);
        $videoFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $videoExtensions)) {
                // tambahkan path lengkap
                $videoFiles[] = $folder . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $videoFiles;
    }
}
