<?php

namespace App\Http\Controllers;

use App\Models\Content;
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

        Content::updateOrCreate([
            'name' => $data['query'],
            'type' => $data['type']
        ]);

        try {
            $response = Http::get($jackettUrl, [
                'apikey' => $apiKey,
                't' => 'search',
                'q' => $query,
            ]);

            if ($response->failed()) {
                return back()->withErrors(['Gagal menghubungi Jackett. Pastikan service-nya aktif.']);
            }

            $xml = simplexml_load_string($response->body());

            foreach ($xml->channel->item ?? [] as $item) {
                $results[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'size' => isset($item->size) ? $this->formatBytes((int) $item->size) : null,
                ];
            }
        } catch (\Exception $e) {
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()]);
        }
        
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

}
