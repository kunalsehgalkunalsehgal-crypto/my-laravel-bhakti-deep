<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diyas', function (Blueprint $table) {
            if (!Schema::hasColumn('diyas', 'mantra_audio_id')) {
                $table->foreignId('mantra_audio_id')
                    ->nullable()
                    ->after('fixed_deity_id')
                    ->constrained('audio_library')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('diyas', function (Blueprint $table) {
            if (Schema::hasColumn('diyas', 'mantra_audio_id')) {
                $table->dropConstrainedForeignId('mantra_audio_id');
            }
        });
    }
};
