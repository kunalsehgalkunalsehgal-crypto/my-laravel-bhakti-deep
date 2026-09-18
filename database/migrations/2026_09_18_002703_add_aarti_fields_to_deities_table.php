<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deities', function (Blueprint $table) {

            if (!Schema::hasColumn('deities', 'aarti_video')) {
                $table->string('aarti_video')
                    ->nullable()
                    ->after('temple_background_image');
            }

            if (!Schema::hasColumn('deities', 'aarti_audio_id')) {
                $table->foreignId('aarti_audio_id')
                    ->nullable()
                    ->after('mantra_audio_id')
                    ->constrained('audio_library')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('deities', function (Blueprint $table) {

            if (Schema::hasColumn('deities', 'aarti_audio_id')) {
                $table->dropConstrainedForeignId('aarti_audio_id');
            }

            if (Schema::hasColumn('deities', 'aarti_video')) {
                $table->dropColumn('aarti_video');
            }
        });
    }
};