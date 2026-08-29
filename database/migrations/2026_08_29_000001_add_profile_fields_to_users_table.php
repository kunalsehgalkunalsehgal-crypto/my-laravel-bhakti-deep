<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('email');
            $table->string('gotra')->nullable()->after('dob');
            $table->string('birth_place')->nullable()->after('gotra');
            $table->text('address')->nullable()->after('birth_place');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dob', 'gotra', 'birth_place', 'address']);
        });
    }
};
