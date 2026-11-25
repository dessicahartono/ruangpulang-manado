<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Pengurus contoh. Divisinya ikut dibuat kalau tidak ditentukan.
 *
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        $first = fake()->firstName();
        $fullName = $first.' '.fake()->lastName();

        return [
            'division_id' => Division::factory(),
            'full_name' => $fullName,
            'nickname' => $first,
            'instagram' => 'https://www.instagram.com/'.Str::of($fullName)->lower()->replace(' ', '.')->ascii().'/',
            'photo' => null,
            'role' => null,
            'bio' => fake()->randomElement([
                'Senang mendengarkan cerita teman-teman.',
                'Suka menulis dan berjalan sore.',
                'Percaya bahwa obrolan kecil bisa berarti besar.',
            ]),
            'position' => fake()->numberBetween(0, 50),
        ];
    }

    /** Pengurus yang belum punya Instagram, untuk menguji penanda kelengkapan data. */
    public function withoutInstagram(): static
    {
        return $this->state(fn () => ['instagram' => null]);
    }
}
