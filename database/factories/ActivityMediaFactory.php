<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Media contoh memakai foto kegiatan yang sudah ada di public/images/kegiatan,
 * jadi gambarnya benar-benar tampil di halaman.
 *
 * @extends Factory<ActivityMedia>
 */
class ActivityMediaFactory extends Factory
{
    private const PHOTOS = [
        'images/kegiatan/pertemuan.jpg',
        'images/kegiatan/sharing.jpg',
        'images/kegiatan/olahraga.jpg',
        'images/kegiatan/musik.jpg',
        'images/kegiatan/nulis.jpg',
        'images/kegiatan/ruang-gerak.jpg',
    ];

    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'path' => fake()->randomElement(self::PHOTOS),
            'type' => ActivityMedia::TYPE_IMAGE,
            'alt' => 'Foto kegiatan contoh',
            'focus' => null,
            'position' => 0,
        ];
    }

    /** Video contoh. Path-nya tidak menunjuk file sungguhan, jadi hanya untuk pengujian. */
    public function video(): static
    {
        return $this->state(fn () => [
            'path' => 'uploads/kegiatan/contoh.mp4',
            'type' => ActivityMedia::TYPE_VIDEO,
            'alt' => 'Video kegiatan contoh',
        ]);
    }
}
