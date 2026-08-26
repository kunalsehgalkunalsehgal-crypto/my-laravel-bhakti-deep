<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pandit_services')
            ->orderBy('id')
            ->select(['id', 'service_type', 'service_name'])
            ->chunkById(100, function ($services) {
                foreach ($services as $service) {
                    DB::table('pandit_services')
                        ->where('id', $service->id)
                        ->update([
                            'service_type' => strtolower(trim((string) $service->service_type)),
                            'service_name' => trim((string) $service->service_name),
                        ]);
                }
            });

        DB::table('pandit_services')
            ->select([
                'pandit_id',
                'service_type',
                'service_name',
                DB::raw('MIN(id) as keep_id'),
            ])
            ->groupBy('pandit_id', 'service_type', 'service_name')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($duplicate) {
                $rows = DB::table('pandit_services')
                    ->where('pandit_id', $duplicate->pandit_id)
                    ->where('service_type', $duplicate->service_type)
                    ->where('service_name', $duplicate->service_name)
                    ->orderBy('id')
                    ->get();

                $keep = $rows->firstWhere('id', $duplicate->keep_id) ?: $rows->first();
                $updates = [];

                foreach (['experience_years', 'approx_performed', 'duration_minutes'] as $field) {
                    $value = $rows->pluck($field)->first(fn ($item) => $item !== null && $item !== '');

                    if ($value !== null && $value !== '') {
                        $updates[$field] = $value;
                    }
                }

                foreach (['approved', 'verified', 'pending', 'rejected'] as $status) {
                    if ($rows->contains('status', $status)) {
                        $updates['status'] = $status;
                        break;
                    }
                }

                if ($updates) {
                    $updates['updated_at'] = now();
                    DB::table('pandit_services')->where('id', $keep->id)->update($updates);
                }

                DB::table('pandit_services')
                    ->whereIn('id', $rows->where('id', '!=', $keep->id)->pluck('id')->all())
                    ->delete();
            });

        Schema::table('pandit_services', function (Blueprint $table) {
            $table->unique(['pandit_id', 'service_type', 'service_name'], 'pandit_services_unique_pandit_type_name');
        });
    }

    public function down(): void
    {
        Schema::table('pandit_services', function (Blueprint $table) {
            $table->dropUnique('pandit_services_unique_pandit_type_name');
        });
    }
};
