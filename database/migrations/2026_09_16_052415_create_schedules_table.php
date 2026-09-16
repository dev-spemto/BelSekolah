<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('day'); // monday, tuesday, wednesday, thursday, friday, saturday, sunday
            $table->string('variant')->default('default'); // 'default', 'jumat_jamaah', 'jumat_ringkas'
            $table->time('time'); // Format HH:MM:SS (misal: 07:00:00)
            $table->string('event_name'); // Bel Masuk, Istirahat, Bel Pulang, dll.
            $table->string('audio_file'); // Reference ke nama file mp3 (misal: bel_masuk.mp3)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};