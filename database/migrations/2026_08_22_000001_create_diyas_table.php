<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diyas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_deity_id')->nullable()->constrained('deities')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->decimal('seva_amount', 12, 2)->default(0);
            $table->string('duration')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('deity_selection_mode')->default('user_select')->index();
            $table->text('mantra_ambience')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diyas');
    }
};
