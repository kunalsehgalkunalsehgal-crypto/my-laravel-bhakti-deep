<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pandit_zoom_connections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->unique()
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->string('zoom_user_id')->nullable();
            $table->string('zoom_email')->nullable();

            $table->text('access_token');
            $table->text('refresh_token');

            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('connected_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pandit_zoom_connections');
    }
};