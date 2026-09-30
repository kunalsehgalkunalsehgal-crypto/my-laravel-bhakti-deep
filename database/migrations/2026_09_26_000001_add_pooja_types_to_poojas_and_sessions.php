<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poojas', function (Blueprint $table) {
            if (! Schema::hasColumn('poojas', 'live_pooja_enabled')) {
                $table->boolean('live_pooja_enabled')->default(true)->after('base_price');
            }

            if (! Schema::hasColumn('poojas', 'live_pooja_title')) {
                $table->string('live_pooja_title')->default('Live Pooja')->after('live_pooja_enabled');
            }

            if (! Schema::hasColumn('poojas', 'live_pooja_description')) {
                $table->text('live_pooja_description')->nullable()->after('live_pooja_title');
            }

            if (! Schema::hasColumn('poojas', 'live_pooja_price')) {
                $table->decimal('live_pooja_price', 12, 2)->default(0)->after('live_pooja_description');
            }

            if (! Schema::hasColumn('poojas', 'digital_pooja_enabled')) {
                $table->boolean('digital_pooja_enabled')->default(true)->after('live_pooja_price');
            }

            if (! Schema::hasColumn('poojas', 'digital_pooja_title')) {
                $table->string('digital_pooja_title')->default('Digital Pooja')->after('digital_pooja_enabled');
            }

            if (! Schema::hasColumn('poojas', 'digital_pooja_description')) {
                $table->text('digital_pooja_description')->nullable()->after('digital_pooja_title');
            }

            if (! Schema::hasColumn('poojas', 'digital_pooja_price')) {
                $table->decimal('digital_pooja_price', 12, 2)->default(0)->after('digital_pooja_description');
            }

            if (! Schema::hasColumn('poojas', 'digital_pooja_video')) {
                $table->string('digital_pooja_video')->nullable()->after('digital_pooja_price');
            }
        });

        DB::table('poojas')->chunkById(100, function ($poojas) {
            foreach ($poojas as $pooja) {
                DB::table('poojas')
                    ->where('id', $pooja->id)
                    ->update([
                        'live_pooja_title' => $pooja->live_pooja_title ?: 'Live Pooja',
                        'live_pooja_description' => $pooja->live_pooja_description ?: 'Join the pooja live with your personalized sankalp.',
                        'live_pooja_price' => $pooja->live_pooja_price ?: $pooja->base_price,
                        'digital_pooja_title' => $pooja->digital_pooja_title ?: 'Digital Pooja',
                        'digital_pooja_description' => $pooja->digital_pooja_description ?: 'Receive a digital video of the pooja performed for your sankalp.',
                        'digital_pooja_price' => $pooja->digital_pooja_price ?: $pooja->base_price,
                    ]);
            }
        });

        Schema::table('pooja_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('pooja_sessions', 'pooja_type')) {
                $table->string('pooja_type')->nullable()->after('ritual_slug')->index();
            }

            if (! Schema::hasColumn('pooja_sessions', 'pooja_type_title')) {
                $table->string('pooja_type_title')->nullable()->after('pooja_type');
            }

            if (! Schema::hasColumn('pooja_sessions', 'pooja_type_price')) {
                $table->decimal('pooja_type_price', 12, 2)->nullable()->after('pooja_type_title');
            }

            if (! Schema::hasColumn('pooja_sessions', 'digital_video_path')) {
                $table->string('digital_video_path')->nullable()->after('pooja_type_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pooja_sessions', function (Blueprint $table) {
            foreach (['digital_video_path', 'pooja_type_price', 'pooja_type_title', 'pooja_type'] as $column) {
                if (Schema::hasColumn('pooja_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('poojas', function (Blueprint $table) {
            foreach ([
                'digital_pooja_video',
                'digital_pooja_price',
                'digital_pooja_description',
                'digital_pooja_title',
                'digital_pooja_enabled',
                'live_pooja_price',
                'live_pooja_description',
                'live_pooja_title',
                'live_pooja_enabled',
            ] as $column) {
                if (Schema::hasColumn('poojas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
