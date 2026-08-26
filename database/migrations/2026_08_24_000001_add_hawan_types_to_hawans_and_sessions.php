<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hawans', function (Blueprint $table) {
            if (!Schema::hasColumn('hawans', 'samuhik_hawan_enabled')) {
                $table->boolean('samuhik_hawan_enabled')->default(true)->after('base_price');
            }

            if (!Schema::hasColumn('hawans', 'samuhik_hawan_title')) {
                $table->string('samuhik_hawan_title')->default('Samuhik Hawan')->after('samuhik_hawan_enabled');
            }

            if (!Schema::hasColumn('hawans', 'samuhik_hawan_description')) {
                $table->text('samuhik_hawan_description')->nullable()->after('samuhik_hawan_title');
            }

            if (!Schema::hasColumn('hawans', 'samuhik_hawan_price')) {
                $table->decimal('samuhik_hawan_price', 12, 2)->default(0)->after('samuhik_hawan_description');
            }

            if (!Schema::hasColumn('hawans', 'special_hawan_enabled')) {
                $table->boolean('special_hawan_enabled')->default(true)->after('samuhik_hawan_price');
            }

            if (!Schema::hasColumn('hawans', 'special_hawan_title')) {
                $table->string('special_hawan_title')->default('Special Hawan')->after('special_hawan_enabled');
            }

            if (!Schema::hasColumn('hawans', 'special_hawan_description')) {
                $table->text('special_hawan_description')->nullable()->after('special_hawan_title');
            }

            if (!Schema::hasColumn('hawans', 'special_hawan_price')) {
                $table->decimal('special_hawan_price', 12, 2)->default(0)->after('special_hawan_description');
            }
        });

        DB::table('hawans')->chunkById(100, function ($hawans) {
            foreach ($hawans as $hawan) {
                DB::table('hawans')
                    ->where('id', $hawan->id)
                    ->update([
                        'samuhik_hawan_title' => $hawan->samuhik_hawan_title ?: 'Samuhik Hawan',
                        'samuhik_hawan_description' => $hawan->samuhik_hawan_description ?: 'Personalized sankalp, live access and digital receipt.',
                        'samuhik_hawan_price' => $hawan->samuhik_hawan_price ?: $hawan->base_price,
                        'special_hawan_title' => $hawan->special_hawan_title ?: 'Special Hawan',
                        'special_hawan_description' => $hawan->special_hawan_description ?: 'Priority slot, extended ritual, family join and replay.',
                        'special_hawan_price' => $hawan->special_hawan_price ?: ((float) $hawan->base_price + 2500),
                    ]);
            }
        });

        Schema::table('hawan_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('hawan_sessions', 'hawan_type')) {
                $table->string('hawan_type')->nullable()->after('ritual_slug')->index();
            }

            if (!Schema::hasColumn('hawan_sessions', 'hawan_type_title')) {
                $table->string('hawan_type_title')->nullable()->after('hawan_type');
            }

            if (!Schema::hasColumn('hawan_sessions', 'hawan_type_price')) {
                $table->decimal('hawan_type_price', 12, 2)->nullable()->after('hawan_type_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hawan_sessions', function (Blueprint $table) {
            foreach (['hawan_type_price', 'hawan_type_title', 'hawan_type'] as $column) {
                if (Schema::hasColumn('hawan_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('hawans', function (Blueprint $table) {
            foreach ([
                'special_hawan_price',
                'special_hawan_description',
                'special_hawan_title',
                'special_hawan_enabled',
                'samuhik_hawan_price',
                'samuhik_hawan_description',
                'samuhik_hawan_title',
                'samuhik_hawan_enabled',
            ] as $column) {
                if (Schema::hasColumn('hawans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
