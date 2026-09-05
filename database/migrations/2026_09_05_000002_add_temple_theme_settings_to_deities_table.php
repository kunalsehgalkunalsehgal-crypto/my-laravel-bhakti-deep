<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deities', function (Blueprint $table) {
            if (!Schema::hasColumn('deities', 'temple_background_image')) {
                $table->string('temple_background_image')->nullable()->after('featured_image');
            }

            if (!Schema::hasColumn('deities', 'primary_color')) {
                $table->string('primary_color', 20)->nullable()->after('temple_background_image');
            }

            if (!Schema::hasColumn('deities', 'secondary_color')) {
                $table->string('secondary_color', 20)->nullable()->after('primary_color');
            }

            if (!Schema::hasColumn('deities', 'glow_color')) {
                $table->string('glow_color', 20)->nullable()->after('secondary_color');
            }

            if (!Schema::hasColumn('deities', 'ambient_audio_id')) {
                $table->foreignId('ambient_audio_id')
                    ->nullable()
                    ->after('glow_color')
                    ->constrained('audio_library')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('deities', 'particle_style')) {
                $table->string('particle_style')->nullable()->after('ambient_audio_id');
            }

            if (!Schema::hasColumn('deities', 'flame_style')) {
                $table->string('flame_style')->nullable()->after('particle_style');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deities', function (Blueprint $table) {
            if (Schema::hasColumn('deities', 'ambient_audio_id')) {
                $table->dropConstrainedForeignId('ambient_audio_id');
            }

            foreach ([
                'flame_style',
                'particle_style',
                'glow_color',
                'secondary_color',
                'primary_color',
                'temple_background_image',
            ] as $column) {
                if (Schema::hasColumn('deities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
