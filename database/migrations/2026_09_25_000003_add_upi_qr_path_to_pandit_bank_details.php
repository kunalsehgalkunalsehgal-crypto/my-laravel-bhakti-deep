<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandit_bank_details', function (Blueprint $table) {
            $table->string('upi_qr_path')->nullable()->after('upi_id');
        });
    }

    public function down(): void
    {
        Schema::table('pandit_bank_details', function (Blueprint $table) {
            $table->dropColumn('upi_qr_path');
        });
    }
};
