<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pandit_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pandit_id')->constrained('pandits')->onDelete('cascade');
            $table->enum('sender', ['pandit', 'admin']);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pandit_messages');
    }
};
