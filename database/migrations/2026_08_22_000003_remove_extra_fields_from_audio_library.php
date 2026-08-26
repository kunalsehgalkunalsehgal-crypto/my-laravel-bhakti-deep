<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_library', function (Blueprint $table) {
            foreach (['duration_seconds', 'language', 'is_premium'] as $column) {
                if (Schema::hasColumn('audio_library', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('audio_library', function (Blueprint $table) {
            if (!Schema::hasColumn('audio_library', 'duration_seconds')) {
                $table->unsignedInteger('duration_seconds')->nullable()->after('audio_file');
            }

            if (!Schema::hasColumn('audio_library', 'language')) {
                $table->string('language')->nullable()->after('duration_seconds');
            }

            if (!Schema::hasColumn('audio_library', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('language');
            }
        });
    }
};
