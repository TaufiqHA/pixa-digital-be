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
        ])->get("{$this->baseUrl}/api/v2/torrents/info", [
            'filter' => 'downloading',
        ]);

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
        ])->get("{$this->baseUrl}/api/v2/torrents/info", [
            'filter' => 'downloading',
        ]);

        if (!$response->successful()) {
            return view('jackett.download', ['error' => 'Gagal mengambil data torrent', 'torrents' => []]);
        }

        $torrents = $response->json();

        return view('qbittorrent.index', ['torrents' => $torrents, 'error' => null]);
    }

    public function downloadedTorrent()
    {
        $contents = Content::whereIn('status', ['converted', 'converting'])->get();

        return view('qbittorrent.downloaded', ['contents' => $contents, 'error' => null]);
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

        return redirect()->route('qbittorrent.downloadInfo')->with('success', 'Torrent berhasil di-pause.');
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

}
