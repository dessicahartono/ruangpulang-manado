<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->text('description');
            // Hanya dari data awal, misalnya kartu Menulis yang menautkan ke Blog. Tidak diubah lewat form.
            $table->string('link_url')->nullable();
            $table->string('link_label')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();
        });

        // Satu kegiatan punya satu sampai tiga foto atau video.
        Schema::create('activity_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('type', 10);
            $table->string('alt')->nullable();
            // Titik fokus foto (CSS object-position), hanya dari data awal.
            $table->string('focus')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_media');
        Schema::dropIfExists('activities');
    }
};
