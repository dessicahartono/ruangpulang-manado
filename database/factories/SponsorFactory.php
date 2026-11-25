<?php

namespace Database\Factories;

use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsor>
 */
class SponsorFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'photo' => null,
            'description' => $name.' mendukung kegiatan Ruang Pulang sebagai sponsor contoh.',
            'link' => 'https://www.'.fake()->domainName(),
            'position' => fake()->unique()->numberBetween(100, 999),
        ];
    }

    /** Sponsor yang belum punya link. */
    public function withoutLink(): static
    {
        return $this->state(fn () => ['link' => null]);
    }
}
