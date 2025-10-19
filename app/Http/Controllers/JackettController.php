<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Torrent;
use App\Services\RadarrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class JackettController extends Controller
{
    public function index()
    {
        $results = [];
        return view('jackett.index', compact('results'));
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $jackettUrl = 'http://localhost:9117/api/v2.0/indexers/all/results/torznab';
        $apiKey = 'qx3u290g3d8e6c7bsazxr0hkolkk19p0';
        $results = [];

        $validator = Validator::make($request->all(), [
            'query' => 'required',
            'type' => 'required'
        ]);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validate();

        session(['query' => $data['query']]);
        session(['type' => $data['type']]);

        // session(['content_id' => $content->id]);

        try {
            $response = Http::get($jackettUrl, [
                'apikey' => $apiKey,
                't' => 'search',
                'q' => $query,
                'cat' => '2000,2020,2030,2040'
            ]);

            if ($response->failed()) {
                return back()->withErrors(['Gagal menghubungi Jackett. Pastikan service-nya aktif.']);
            }

            $xml = simplexml_load_string($response->body());

            foreach ($xml->channel->item ?? [] as $item) {
                $results[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'size' => isset($item->size) ? (int) $item->size : 0,
                ];
            }

        } catch (\Exception $e) {
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()]);
        }

        $results = collect($results)
        ->sortByDesc('size')
        ->map(function ($item) {
            $item['size'] = $this->formatBytes($item['size']);
                return $item;
            })
        ->values() // reset indeks
        ->toArray();
        
        return view('jackett.index', [
            'results' => $results,
            'query' => $query,
        ]);
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes <= 0) {
            return '0 B';
        }

        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        $bytes /= pow(1024, $power);

        return round($bytes, $precision) . ' ' . $units[$power];
    }

    public function addToBittorrent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'size'          => 'nullable|min:0',
            'magnet_uri'    => 'required',
            'status'        => 'required|in:queued,downloading,completed',
        ]);

        // dd($validator);

        if ($validator->fails())
        {
            return redirect()->to(route('jackett.index'))->withErrors($validator->errors()->messages());
        }

        $validated = $validator->validate();

        $content = Content::updateOrCreate([
            'name' => session('query'),
            'type' => session('type')
        ]);

        $torrent = Torrent::create([
            'content_id'    => $content->id,
            'hash'          => "",
            'name'          => $validated['name'],
            'size'          => $validated['size'] ?? 0,
            'status'        => $validated['status'],
        ]);

        $qbittorrentController = new QbittorrentController();
        $qbittorrentController->addTorrentWithAuth(new Request([
            'torrent_url' => $validated['magnet_uri'],
            'torrent_title' => $validated['name'],
        ]), $torrent);

        return redirect()->to(route('jackett.index'))->with('success', 'Torrent berhasil ditambahkan ke qBittorrent');
    }

}
