<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pooja_sessions', function (Blueprint $table) {
            $table->string('booking_mode')->nullable()->default(null)->change();
        });

        Schema::table('poojas', function (Blueprint $table) {
            if (! Schema::hasColumn('poojas', 'digital_pooja_audio_id')) {
                $table->foreignId('digital_pooja_audio_id')
                    ->nullable()
                    ->after('digital_pooja_video')
                    ->constrained('audio_library')
                    ->nullOnDelete();
            }
        });

        Schema::table('pooja_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('pooja_sessions', 'digital_audio_id')) {
                $table->foreignId('digital_audio_id')
                    ->nullable()
                    ->after('digital_video_path')
                    ->constrained('audio_library')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pooja_sessions', 'digital_audio_title')) {
                $table->string('digital_audio_title')->nullable()->after('digital_audio_id');
            }

            if (! Schema::hasColumn('pooja_sessions', 'digital_audio_path')) {
                $table->string('digital_audio_path')->nullable()->after('digital_audio_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pooja_sessions', function (Blueprint $table) {
            foreach (['digital_audio_path', 'digital_audio_title'] as $column) {
                if (Schema::hasColumn('pooja_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('pooja_sessions', 'digital_audio_id')) {
                $table->dropConstrainedForeignId('digital_audio_id');
            }
        });

        Schema::table('poojas', function (Blueprint $table) {
            if (Schema::hasColumn('poojas', 'digital_pooja_audio_id')) {
                $table->dropConstrainedForeignId('digital_pooja_audio_id');
            }
        });

        DB::table('pooja_sessions')->whereNull('booking_mode')->update(['booking_mode' => 'online']);

        Schema::table('pooja_sessions', function (Blueprint $table) {
            $table->string('booking_mode')->default('online')->nullable(false)->change();
        });
    }
};
