<?php

namespace Database\Seeders;

use App\Models\Album;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $albums = [

           // Personal

['title' => 'Islanda', 'year' => 2026, 'type' => 'personal'],
['title' => 'Dolomiti', 'year' => 2026, 'type' => 'personal'],
['title' => 'Maspalomas', 'year' => 2025, 'type' => 'personal'],
['title' => 'Street Photography', 'year' => 2025, 'type' => 'personal'],
['title' => 'Tenerife', 'year' => 2024, 'type' => 'personal'],
['title' => 'Madrid', 'year' => 2024, 'type' => 'personal'],

            // Work

            ['title' => 'Matrimonio Luca e Sara', 'year' => 2026, 'type' => 'work'],
            ['title' => 'Villa Rossi', 'year' => 2026, 'type' => 'work'],
            ['title' => 'Casa Bianchi', 'year' => 2025, 'type' => 'work'],
            ['title' => 'Shooting Giulia', 'year' => 2025, 'type' => 'work'],
        ];

        foreach ($albums as $album) {

            Album::updateOrCreate(
                [
                    'title' => $album['title'],
                ],
                $album
            );

        }
    }
}
