<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityMedia;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /** Aman dijalankan ulang: kegiatan yang sudah ada, termasuk medianya, tidak ditimpa. */
    public function run(): void
    {
        foreach (require resource_path('data/activities.php') as $position => $item) {
            $activity = Activity::firstOrCreate(['title' => $item['title']], [
                'description' => $item['text'],
                'link_url' => isset($item['link_route']) ? route($item['link_route'], absolute: false) : null,
                'link_label' => $item['link_label'] ?? null,
                'position' => $position,
            ]);

            if ($activity->wasRecentlyCreated) {
                $activity->media()->create([
                    'path' => $item['image'],
                    'type' => ActivityMedia::TYPE_IMAGE,
                    'alt' => $item['alt'],
                    'focus' => $item['position'] ?? null,
                    'position' => 0,
                ]);
            }
        }
    }
}
