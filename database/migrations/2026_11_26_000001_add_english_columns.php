<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Versi bahasa Inggris untuk konten dari database. Semuanya boleh kosong:
 * kalau kosong, halaman berbahasa Inggris menampilkan versi Indonesianya.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'posts' => ['title_en' => 'string', 'excerpt_en' => 'text', 'body_en' => 'text'],
        'activities' => ['title_en' => 'string', 'description_en' => 'text', 'link_label_en' => 'string'],
        'sponsors' => ['description_en' => 'text'],
        'divisions' => ['name_en' => 'string', 'description_en' => 'text'],
        'members' => ['role_en' => 'string', 'bio_en' => 'text', 'bio_more_en' => 'text'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column => $type) {
                    $blueprint->{$type}($column)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn(array_keys($columns)));
        }
    }
};
