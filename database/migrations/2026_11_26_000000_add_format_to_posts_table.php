<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** "text" untuk artikel lama dan kiriman pengunjung, "html" untuk artikel dari TinyMCE di halaman admin. */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('format', 10)->default('text')->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('format');
        });
    }
};
