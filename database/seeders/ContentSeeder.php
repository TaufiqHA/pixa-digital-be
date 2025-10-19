<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Content::create([
            [
                'type' => 'movie',
                'name' => 'Inception',
                'title' => 'isekai nonbiri nouka (2010)',
                'description' => 'A skilled thief is given a chance at redemption if he can successfully perform inception: planting an idea into someone’s subconscious.',
                'release_year' => 2010,
                'rating' => 8.8,
                'duration' => 148,
                'cover_image' => 'covers/inception.jpg',
                'file_path' => 'movies/inception.mp4',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
            ]);
    }
}
