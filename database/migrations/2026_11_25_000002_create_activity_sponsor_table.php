<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tabel penghubung many-to-many: satu sponsor bisa mendukung banyak kegiatan, dan sebaliknya. */
    public function up(): void
    {
        Schema::create('activity_sponsor', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['activity_id', 'sponsor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_sponsor');
    }
};
