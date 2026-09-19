<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_files', function (Blueprint $table) {
            $table->id();
            $table->string('title');        // Nama/label suara (misal: Bel Masuk Reguler)
            $table->string('filename');     // Nama file physical (misal: bel_masuk.mp3)
            $table->string('category')->default('umum'); // umum, jumat, darurat, pengumuman
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};