<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Kegiatan contoh. Panggil withMedia() supaya kegiatannya langsung punya foto,
 * karena setiap kegiatan wajib punya satu sampai tiga media.
 *
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Piknik', 'Nonton Bareng', 'Kelas Menulis', 'Jalan Pagi', 'Diskusi Buku', 'Bersih Pantai']).' '.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->randomElement([
                'Kegiatan santai untuk saling mengenal dan berbagi cerita.',
                'Ruang untuk belajar hal baru bersama teman-teman komunitas.',
                'Bergerak bersama sambil menikmati udara Manado.',
            ]),
            'link_url' => null,
            'link_label' => null,
            'position' => fake()->unique()->numberBetween(100, 999),
        ];
    }

    /** Satu sampai tiga foto, sesuai batas Activity::MAX_MEDIA. */
    public function withMedia(int $count = 1): static
    {
        return $this->has(
            ActivityMedia::factory()->count(min($count, Activity::MAX_MEDIA))->sequence(fn ($sequence) => ['position' => $sequence->index]),
            'media',
        );
    }

    public function withLink(string $url = '/blog', string $label = 'Kunjungi Laman'): static
    {
        return $this->state(fn () => ['link_url' => $url, 'link_label' => $label]);
    }
}
