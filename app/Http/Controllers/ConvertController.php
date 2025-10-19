<?php

namespace App\Http\Controllers;

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
            'hash' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        // Step 1: Login ke qBittorrent
        $login = Http::asForm()->post("{$this->baseUrl}/api/v2/auth/login", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($login->body() !== 'Ok.') {
            return back()->with('error', 'Login gagal ke qBittorrent: ' . $login->body());
        }

        $sid = $login->cookies()->getCookieByName('SID')->getValue();

        // Step 2: Ambil semua torrent info
        $response = Http::withHeaders([
            'Cookie' => 'SID=' . $sid,
        ])->get("{$this->baseUrl}/api/v2/torrents/info");

        $torrents = json_decode($response->body(), true);

        // Step 3: Filter torrent berdasarkan hash
        $torrent = collect($torrents)->firstWhere('hash', $validated['hash']);
        if (!$torrent) {
            return back()->with('error', 'Torrent dengan hash tersebut tidak ditemukan.');
        }

        // Step 4: Cari data torrent di database
        $dataTorrent = Torrent::where('name', $torrent['name'])->first();

        if (!$dataTorrent) {
            return back()->with('error', 'Data torrent tidak ditemukan di database.');
        }

        // Step 5: Cari file utama (video)
        $downloadPath = $this->getMainFilePath(folderName: $torrent['content_path']);

        $dataTorrent->update([
            'download_path' => $downloadPath ? $downloadPath[0] : null,
            'hash' => $torrent['hash'],
            'status' => 'converting',
        ]);

        // Step 6: Dispatch job untuk konversi di background
        ConvertToHlsJob::dispatch($dataTorrent);

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
