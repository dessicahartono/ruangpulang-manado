<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('description')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();
        });

        // Satu orang bisa berada di beberapa divisi, jadi setiap penempatan punya barisnya sendiri.
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->restrictOnDelete();
            $table->string('full_name');
            $table->string('nickname');
            $table->string('instagram')->nullable();
            $table->string('photo')->nullable();
            $table->string('role')->nullable();
            $table->text('bio')->nullable();
            $table->text('bio_more')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['division_id', 'full_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
        Schema::dropIfExists('divisions');
    }
};
