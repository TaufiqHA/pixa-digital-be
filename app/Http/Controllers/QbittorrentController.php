<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Torrent;
use Illuminate\Http\Request;
use App\Jobs\ConvertToHlsJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class QbittorrentController extends Controller
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

    public function index()
    {
        // Step 1: Login
        $login = Http::asForm()->post("{$this->baseUrl}/api/v2/auth/login", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($login->body() !== 'Ok.') {
            return response()->json(['error' => 'Login gagal ke qBittorrent: ' . $login->body()], 500);
        }

        $sid = $login->cookies()->getCookieByName('SID')->getValue();

        // Step 2: Panggil /api/v2/torrents/info
        $response = Http::withHeaders([
            'Cookie' => 'SID=' . $sid,
        ])->get("{$this->baseUrl}/api/v2/torrents/info");

        if (!$response->successful()) {
            return response()->json(['error' => 'Gagal mengambil data torrent'], 500);
        }

        $torrents = $response->json();

        return $torrents;
    }

    public function addTorrentWithAuth(Request $request, $torrent)
    {
        $request->validate([
            'torrent_url' => 'required|string',
            'torrent_title' => 'required|string',
        ]);

        // Step 1: Login
        $login = Http::asForm()->post("{$this->baseUrl}/api/v2/auth/login", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($login->body() !== 'Ok.') {
            return redirect()->back()->with('error', 'Login gagal ke qBittorrent: ' . $login->body());
        }

        $sid = $login->cookies()->getCookieByName('SID')->getValue();

        $savepath = storage_path('app/public/films'); // Ganti sesuai path yang diinginkan

        // Step 2: Tambah torrent
        $add = Http::withHeaders([
            'Cookie' => 'SID=' . $sid,
        ])->asForm()->post("{$this->baseUrl}/api/v2/torrents/add", [
            'urls' => $request->torrent_url,
            'savepath' => $savepath, // Ganti sesuai path yang diinginkan
        ]);

        if ($add->successful()) {
            // Simpan informasi ke database dalam tabel content
            Torrent::where('content_id', $torrent->content_id,)->update([
                'status' => 'downloading', // Atau jenis lain sesuai kebutuhan
            ]);
            
            return redirect()->to(route('jackett.index'))->with('success', 'Torrent berhasil ditambahkan ke qBittorrent');
        }

        return redirect()->back()->with('error', 'Gagal menambahkan torrent ke qBittorrent: ' . $add->body());
    }

    public function downloadInfo()
    {
        // Step 1: Login
        $login = Http::asForm()->post("{$this->baseUrl}/api/v2/auth/login", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($login->body() !== 'Ok.') {
            return view('jackett.download', ['error' => 'Login gagal ke qBittorrent: ' . $login->body(), 'torrents' => []]);
        }

        $sid = $login->cookies()->getCookieByName('SID')->getValue();

        // Step 2: Panggil /api/v2/torrents/info untuk download yang sedang berlangsung
        $response = Http::withHeaders([
            'Cookie' => 'SID=' . $sid,
        ])->get("{$this->baseUrl}/api/v2/torrents/info");

        if (!$response->successful()) {
            return view('jackett.download', ['error' => 'Gagal mengambil data torrent', 'torrents' => []]);
        }

        $torrents = $response->json();

        return view('qbittorrent.index', ['torrents' => $torrents, 'error' => null]);
    }

    public function refresh()
    {
        $torrents = $this->index();

        return response()->json($torrents);
    }

    public function pause(Request $request)
    {
        $request->validate([
            'hash' => 'required|string',
        ]);

        // Step 1: Login
        $login = Http::asForm()->post("{$this->baseUrl}/api/v2/auth/login", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($login->failed() || trim($login->body()) !== 'Ok.') {
            return back()->withErrors(['login' => 'Login gagal ke qBittorrent: ' . $login->body()]);
        }

        $cookie = optional($login->cookies()->getCookieByName('SID'))->getValue();

        if (!$cookie) {
            return back()->withErrors(['cookie' => 'Gagal mengambil SID dari qBittorrent.']);
        }

        // Step 2: Pause (Stop) torrent
        $pause = Http::asForm()
            ->withHeaders([
                'Cookie' => 'SID=' . $cookie,
            ])
            ->post("{$this->baseUrl}/api/v2/torrents/pause", [
                'hashes' => $request->hash,
            ]);

        if ($pause->failed()) {
            return back()->withErrors(['pause' => 'Gagal pause torrent: ' . $pause->body()]);
        }

        return redirect()->route('jackett.downloadInfo')->with('success', 'Torrent berhasil di-pause.');
    }



    public function resume(Request $request)
    {
        $request->validate([
            'hash' => 'required|string',
        ]);

        // Step 1: Login
        $login = Http::asForm()->post("{$this->baseUrl}/api/v2/auth/login", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($login->failed() || trim($login->body()) !== 'Ok.') {
            return back()->withErrors(['login' => 'Login gagal ke qBittorrent: ' . $login->body()]);
        }

        $cookie = optional($login->cookies()->getCookieByName('SID'))->getValue();

        if (!$cookie) {
            return back()->withErrors(['cookie' => 'Gagal mengambil SID dari qBittorrent.']);
        }

        // Step 2: Resume torrent
        $resume = Http::asForm()
            ->withHeaders([
                'Cookie' => 'SID=' . $cookie,
            ])
            ->post("{$this->baseUrl}/api/v2/torrents/resume", [
                'hashes' => $request->hash, // Ganti 'hash' menjadi 'hashes'
            ]);

        if ($resume->failed()) {
            return back()->withErrors(['resume' => 'Gagal resume torrent: ' . $resume->body()]);
        }

        return redirect()->route('jackett.downloadInfo')->with('success', 'Torrent berhasil di-resume.');
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

    public function convertToHls($inputPath, $outputFolder)
    {
        $outputPath = storage_path("app/public/hls/{$outputFolder}");

        // Pastikan folder output ada
        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        $ffmpeg = \FFMpeg\FFMpeg::create();
        $video = $ffmpeg->open($inputPath);

        // Buat format HLS
        $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
        $format->setKiloBitrate(1500); // bitrate (sesuaikan kualitas)

        // Simpan ke HLS (1 playlist + banyak segmen .ts)
        $video->save(
            new \FFMpeg\Format\Video\X264(),
            "{$outputPath}/index.m3u8"
        );

        return true;
    }

}
