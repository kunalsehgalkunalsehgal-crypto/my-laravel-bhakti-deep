<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diya_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('diya_sessions', 'diya_id')) {
                $table->foreignId('diya_id')->nullable()->after('service_id')->constrained('diyas')->nullOnDelete();
            }

            if (!Schema::hasColumn('diya_sessions', 'deity_id')) {
                $table->foreignId('deity_id')->nullable()->after('diya_id')->constrained('deities')->nullOnDelete();
            }

            if (!Schema::hasColumn('diya_sessions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('diya_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('diya_sessions', 'expires_at')) {
                $table->dropColumn('expires_at');
            }

            if (Schema::hasColumn('diya_sessions', 'deity_id')) {
                $table->dropConstrainedForeignId('deity_id');
            }

            if (Schema::hasColumn('diya_sessions', 'diya_id')) {
                $table->dropConstrainedForeignId('diya_id');
            }
        });
    }
};
