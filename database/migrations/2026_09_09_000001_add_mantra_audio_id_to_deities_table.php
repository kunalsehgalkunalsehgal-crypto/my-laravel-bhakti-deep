<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deities', function (Blueprint $table) {
            if (!Schema::hasColumn('deities', 'mantra_audio_id')) {
                $table->foreignId('mantra_audio_id')
                    ->nullable()
                    ->after('glow_color')
                    ->constrained('audio_library')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('deities', function (Blueprint $table) {
            if (Schema::hasColumn('deities', 'mantra_audio_id')) {
                $table->dropConstrainedForeignId('mantra_audio_id');
            }
        });
    }
};
