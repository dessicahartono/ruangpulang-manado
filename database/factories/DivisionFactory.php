<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Division>
 */
class DivisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Ruang '.fake()->randomElement(['Cerita', 'Karya', 'Gerak', 'Belajar', 'Peduli', 'Kreasi', 'Suara', 'Langkah']).' '.fake()->unique()->numberBetween(1, 999),
            'description' => ['Divisi contoh untuk menunjukkan data pengurus dari factory.'],
            'position' => fake()->unique()->numberBetween(100, 999),
        ];
    }
}
