<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            if (!Schema::hasColumn('disputes', 'pandit_response')) {
                $table->text('pandit_response')->nullable()->after('description');
            }

            if (!Schema::hasColumn('disputes', 'pandit_responded_at')) {
                $table->timestamp('pandit_responded_at')->nullable()->after('opened_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            foreach (['pandit_responded_at', 'pandit_response'] as $column) {
                if (Schema::hasColumn('disputes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
