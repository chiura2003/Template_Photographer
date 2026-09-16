<?php

namespace Database\Factories;

use App\Enums\AlbumType;
use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(
            fake()->numberBetween(2, 4),
            true
        );

        return [
            'title' => ucwords($title),
            'year' => fake()->numberBetween(2022, now()->year),
            'type' => fake()->randomElement(AlbumType::cases())->value,
            'description' => fake()->paragraph(),
            'featured' => false,
            'is_published' => true,
            'sort_order' => 0,
        ];
    }
}
