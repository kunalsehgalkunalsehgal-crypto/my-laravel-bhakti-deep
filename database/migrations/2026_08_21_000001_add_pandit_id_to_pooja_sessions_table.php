<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pooja_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('pooja_sessions', 'pandit_id')) {
                $table->foreignId('pandit_id')
                    ->nullable()
                    ->after('sankalp_form_id')
                    ->constrained('pandits')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pooja_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('pooja_sessions', 'pandit_id')) {
                $table->dropConstrainedForeignId('pandit_id');
            }
        });
    }
};
