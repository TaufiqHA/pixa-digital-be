<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SonarrController extends Controller
{
    public function index()
    {
        return view('sonarr.index');
    }
    
    public function search(Request $request)
    {
        $search = $request->input('search');
        
        if (empty($search)) {
            return view('sonarr.index', ['results' => []]);
        }

        $apikey = env('SONARR_API_KEY', 'cbb9afcb26084ddb98ee78c69c04ef1d');
        $baseUrl = env('SONARR_BASE_URL', 'http://localhost:8989');

        $response = Http::withHeaders([
            "X-Api-Key" => $apikey
        ])->get($baseUrl . "/api/v3/series/lookup", [
            "term" => $search, 
        ]);

        if ($response->successful()) {
            $results = $response->json();
            // Filter out any empty results
            $results = array_filter($results, function($item) {
                return !empty($item['title']);
            });
            
            return view('sonarr.index', ['results' => $results]);
        }

        return view('sonarr.index', [
            'results' => [],
            'error' => 'Gagal mengambil data dari Sonarr: ' . $response->body()
        ]);
    }
    
    public function addTvShow(Request $request)
    {
        $data = json_decode($request->input('result'), true);
        
        if (!$data) {
            return redirect()->back()->with('error', 'Invalid data provided');
        }
        
        $apikey = env('SONARR_API_KEY', 'cbb9afcb26084ddb98ee78c69c04ef1d');
        $baseUrl = env('SONARR_BASE_URL', 'http://localhost:8989');
        
        // Prepare the TV show data for Sonarr
        $sonarrData = [
            'title' => $data['title'],
            'year' => $data['year'] ?? null,
            'tvdbId' => $data['tvdbId'] ?? (isset($data['tvdbid']) ? $data['tvdbid'] : null),
            'qualityProfileId' => 1, // Default quality profile
            'titleSlug' => $data['slug'] ?? strtolower(str_replace(' ', '-', $data['title'] ?? 'unknown')),
            'images' => $data['images'] ?? [],
            'path' => "/home/taufiq/Downloads/" . $data['title'], // Default download path
            'monitored' => true,
            'addOptions' => [
                'ignoreEpisodesWithFiles' => true,
                'ignoreEpisodesWithoutFiles' => false,
                'searchForMissingEpisodes' => true
            ]
        ];
        
        // Make API call to add the TV show
        $response = Http::withHeaders([
            "X-Api-Key" => $apikey
        ])->post($baseUrl . "/api/v3/series", $sonarrData);

        if ($response->successful()) {
            return redirect()->back()->with('success', 'TV show added successfully to Sonarr!');
        } else {
            return redirect()->back()->with('error', 'Failed to add TV show to Sonarr: ' . $response->body());
        }
    }
}
