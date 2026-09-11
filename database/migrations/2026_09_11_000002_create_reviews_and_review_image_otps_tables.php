<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('booking_type');
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pandit_id');
            $table->string('review_by');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->unique(['booking_type', 'booking_id', 'review_by']);
        });

        Schema::create('review_image_otps', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('email');
            $table->string('otp');
            $table->string('purpose')->default('review_image_upload');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_image_otps');
        Schema::dropIfExists('reviews');
    }
};
