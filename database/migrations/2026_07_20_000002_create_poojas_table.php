<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poojas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('featured_image')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->string('duration')->nullable();
            $table->string('mode')->default('Live + Replay');
            $table->json('benefits')->nullable();
            $table->json('included_items')->nullable();
            $table->json('session_timeline')->nullable();
            $table->json('donation_options')->nullable();
            $table->json('available_slots')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poojas');
    }
};
