<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->unique()->lexify('photo_??????').'.webp';

        return [
            'album_id' => Album::factory(),
            'title' => ucwords(fake()->words(fake()->numberBetween(2, 5), true)),
            'description' => fake()->optional()->paragraph(),
            'filename' => $filename,
            'filepath' => 'albums/demo/'.$filename,
            'mime_type' => 'image/webp',
            'filesize' => fake()->numberBetween(300000, 8000000),
            'width' => fake()->randomElement([
                1920,
                2048,
                3840,
            ]),
            'height' => fake()->randomElement([
                1080,
                1365,
                2160,
            ]),
            'alt_text' => fake()->optional()->sentence(4),
            'sort_order' => 0,
            'is_published' => true,
        ];
    }
}
