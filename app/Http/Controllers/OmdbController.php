<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OmdbController extends Controller
{
    private $apiKey = '23a78040'; // Ganti dengan API key OMDb mu

    public function search($title)
    {
        if (!$title) {
            return response()->json(['error' => 'Title parameter is required'], 400);
        }

        $response = Http::get('http://www.omdbapi.com/', [
            'apikey' => $this->apiKey,
            't' => $title,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch data from OMDb'], 500);
        }

        $data = $response->json();

        if (isset($data['Error'])) {
            return response()->json(['error' => $data['Error']], 404);
        }

        return response()->json($data);
    }

}
