<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main Pandit Table
        Schema::create('pandits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('full_name')->nullable();
            $table->string('pandit_name')->nullable();
            $table->string('profile_photo')->nullable();

            $table->string('mobile')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();

            $table->text('full_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();

            $table->unsignedInteger('total_experience_years')->nullable();
            $table->text('about')->nullable();

            $table->boolean('associated_with_institution')->default(false);
            $table->string('institution_name')->nullable();
            $table->string('position_role')->nullable();
            $table->string('institution_city')->nullable();

            $table->boolean('is_independent')->default(true);
            $table->string('status')->default('under_review');

            $table->timestamps();
        });


        // Qualification
        Schema::create('pandit_qualifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->string('highest_qualification')->nullable();
            $table->string('course_name')->nullable();
            $table->string('institute_name')->nullable();
            $table->string('guru_name')->nullable();
            $table->year('completion_year')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('certificate_file')->nullable();

            $table->string('sampradaya')->nullable();
            $table->string('ritual_tradition')->nullable();
            $table->string('sanskrit_level')->nullable();

            $table->json('knowledge_areas')->nullable();

            $table->timestamps();
        });


        // Services
        Schema::create('pandit_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->string('service_type');
            $table->string('service_name');

            $table->unsignedInteger('experience_years')->nullable();
            $table->unsignedInteger('approx_performed')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();

            $table->string('status')->default('pending');

            $table->timestamps();
        });


        // Availability Slots
        Schema::create('pandit_availability_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->string('day');
            $table->time('start_time');
            $table->time('end_time');

            $table->boolean('is_available')->default(true);

            $table->timestamps();
        });


        // Availability Settings
        Schema::create('pandit_availability_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->unique()
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->boolean('accept_new_bookings')->default(true);
            $table->unsignedInteger('advance_booking_days')->default(0);

            $table->timestamps();
        });


        // Languages
        Schema::create('pandit_languages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->string('language');
            $table->boolean('can_speak')->default(true);
            $table->boolean('can_conduct_ritual')->default(false);

            $table->timestamps();
        });


        // Online Setup
        Schema::create('pandit_online_setups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->unique()
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->boolean('online_hawan')->default(false);
            $table->boolean('online_pooja')->default(false);
            $table->boolean('stable_internet')->default(false);

            $table->json('platforms')->nullable();
            $table->json('devices')->nullable();
            $table->json('equipment')->nullable();

            $table->timestamps();
        });


        // Documents
        Schema::create('pandit_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->unique()
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->string('government_id_type')->nullable();
            $table->string('government_id_file')->nullable();

            $table->string('pan_number')->nullable();
            $table->string('pan_card_file')->nullable();

            $table->string('address_proof')->nullable();
            $table->string('qualification_certificate')->nullable();

            $table->json('pooja_photos')->nullable();
            $table->json('hawan_photos')->nullable();

            $table->string('mantra_chanting_sample')->nullable();
            $table->string('hawan_performance_video')->nullable();

            $table->string('verification_status')->default('pending');

            $table->timestamps();
        });


        // Bank Details
        Schema::create('pandit_bank_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pandit_id')
                ->unique()
                ->constrained('pandits')
                ->cascadeOnDelete();

            $table->string('account_holder_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('pan_number')->nullable();

            $table->string('verification_status')->default('pending');

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('pandit_bank_details');
        Schema::dropIfExists('pandit_documents');
        Schema::dropIfExists('pandit_online_setups');
        Schema::dropIfExists('pandit_languages');
        Schema::dropIfExists('pandit_availability_settings');
        Schema::dropIfExists('pandit_availability_slots');
        Schema::dropIfExists('pandit_services');
        Schema::dropIfExists('pandit_qualifications');
        Schema::dropIfExists('pandits');
    }
};