<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /** Aman dijalankan ulang: pengurus yang sudah ada tidak ditimpa. */
    public function run(): void
    {
        foreach (require resource_path('data/portofolioteam.php') as $groupPosition => $group) {
            $division = Division::firstOrCreate(['name' => $group['title']], [
                'description' => $group['description'] ?? null,
                'position' => $groupPosition,
            ]);

            foreach ($group['members'] as $position => $member) {
                [$fullName, $nickname] = $this->splitName($member['name']);

                Member::firstOrCreate(['division_id' => $division->id, 'full_name' => $fullName], [
                    'nickname' => $nickname,
                    'instagram' => $member['instagram'],
                    'photo' => $member['photo'],
                    'role' => $member['role'] ?? null,
                    'bio' => $member['bio'] ?? null,
                    'bio_more' => $member['bio_more'] ?? null,
                    'position' => $position,
                ]);
            }
        }
    }

    /** "Fathia Salama Jacob (Alma)" menjadi nama lengkap dan nama panggilan. */
    private function splitName(string $name): array
    {
        return preg_match('/^(.+?)\s*\((.+)\)$/', $name, $parts) === 1
            ? [$parts[1], $parts[2]]
            : [$name, $name];
    }
}
