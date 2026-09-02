<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('disputable_type');
            $table->unsignedBigInteger('disputable_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pandit_id')->nullable()->constrained('pandits')->nullOnDelete();
            $table->string('reason');
            $table->text('description');
            $table->string('status')->default('open')->index();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index(['disputable_type', 'disputable_id'], 'disputes_disputable_index');
        });

        Schema::create('dispute_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained('disputes')->cascadeOnDelete();
            $table->string('uploaded_by_type')->nullable();
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index(['uploaded_by_type', 'uploaded_by_id'], 'dispute_evidences_uploader_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_evidences');
        Schema::dropIfExists('disputes');
    }
};
