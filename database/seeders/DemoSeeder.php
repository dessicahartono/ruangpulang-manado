<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Division;
use App\Models\Member;
use App\Models\Post;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

/**
 * Data contoh dari factory untuk demo pagination, pencarian, dan relasi.
 * Tidak ikut DatabaseSeeder supaya data asli komunitas tidak tercampur data palsu.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Factory membutuhkan paket Faker yang hanya terpasang di lingkungan pengembangan,
 * jadi seeder ini tidak dijalankan di hosting.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Blog: 25 artikel terbit (lima halaman di /blog) dan 3 kiriman yang menunggu persetujuan.
        Post::factory()->count(25)->create();
        Post::factory()->count(3)->pending()->create();

        // Pengurus: satu divisi contoh berisi 12 orang, dua di antaranya belum punya Instagram.
        $division = Division::factory()->create();
        Member::factory()->count(10)->for($division)->sequence(fn ($sequence) => ['position' => $sequence->index])->create();
        Member::factory()->count(2)->for($division)->withoutInstagram()->sequence(fn ($sequence) => ['position' => 10 + $sequence->index])->create();

        // Sponsor dan kegiatan, lalu dihubungkan lewat relasi many-to-many.
        $sponsors = Sponsor::factory()->count(3)->create();
        $activities = collect([1, 2, 3])->map(fn (int $mediaCount) => Activity::factory()->withMedia($mediaCount)->create());

        $activities->each(fn (Activity $activity) => $activity->sponsors()->attach(
            $sponsors->random(fake()->numberBetween(1, 2))->pluck('id'),
        ));
    }
}
