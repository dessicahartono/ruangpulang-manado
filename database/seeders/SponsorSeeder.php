<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /** Aman dijalankan ulang: sponsor yang sudah ada tidak ditimpa. */
    public function run(): void
    {
        foreach (require resource_path('data/sponsors.php') as $position => $sponsor) {
            Sponsor::firstOrCreate(['name' => $sponsor['name']], [
                'description' => $sponsor['description'],
                'position' => $position,
            ]);
        }
    }
}
