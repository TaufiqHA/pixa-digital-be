<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use App\Services\RadarrService;

class RadarrController extends Controller
{
    protected $radarr;

    public function __construct(RadarrService $radarr)
    {
        $this->radarr = $radarr;
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = $this->radarr->searchMovies($query);
        return view('jackett.index', compact('results'));
    }

    public function addMovie(Request $request)
    {
        $data = json_decode($request->input('result'), true);
        if (!isset($data['path'])) {
            $data['path'] = "/home/taufiq/Documents/KERJA/PixaDigital/backend/public/storage/films/" . $data['folder'];
            $data['qualityProfileId'] = 1;
            $data['monitored'] = true;
        }

        // Tambahkan addOptions untuk memicu pencarian dan pengunduhan otomatis setelah penambahan
        if (!isset($data['addOptions'])) {
            $data['addOptions'] = [
                'searchForMovie' => true,
                'monitor' => 'movieOnly'
            ];
        }

        $response = $this->radarr->addMovie($data);

        $dataMovie = $response;

        if(!empty($response))
        {
            Content::create([
                'type' => 'movie',
                'name' => $dataMovie['title'],
                'title' => $dataMovie['title'],
                'description' => $dataMovie['overview'] ?? null,
                'release_year' => isset($dataMovie['year']) ? (int)$dataMovie['year'] : null,
                'rating' => isset($dataMovie['ratings']['imdb']['value']) ? (float)$dataMovie['ratings']['imdb']['value'] : null,
                'duration' => isset($dataMovie['runtime']) ? (int)$dataMovie['runtime'] : null,
                'cover_image' => isset($dataMovie['images'][0]['url']) ? $dataMovie['images'][0]['url'] : null,
                'file_path' => $dataMovie['path'],
                'status' => 'added'
            ]);
        }
        return redirect()->to(route('jackett.downloadInfo', ['results' => []]));
    }

}
