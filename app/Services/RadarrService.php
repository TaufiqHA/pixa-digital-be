<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RadarrService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('RADARR_URL', 'http://localhost:7878/api/v3');
        $this->apiKey = env('RADARR_API_KEY');
    }

    public function getMovies()
    {
        return Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/movie")->json();
    }

    public function addMovie(array $data)
    {
        return Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("{$this->baseUrl}/movie", $data)->json();
    }

    public function searchMovies($query)
    {
        return Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->get("{$this->baseUrl}/movie/lookup", [
            'term' => $query,
        ])->json();
    }
}
