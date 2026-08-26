<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandits', function (Blueprint $table) {
            $table->text('admin_remark')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('admin_remark');
        });
    }

    public function down(): void
    {
        Schema::table('pandits', function (Blueprint $table) {
            $table->dropColumn(['admin_remark', 'reviewed_at']);
        });
    }
};
