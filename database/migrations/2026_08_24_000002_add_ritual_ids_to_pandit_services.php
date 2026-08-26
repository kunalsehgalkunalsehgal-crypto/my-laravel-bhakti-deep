<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandit_services', function (Blueprint $table) {
            if (!Schema::hasColumn('pandit_services', 'hawan_id')) {
                $table->foreignId('hawan_id')->nullable()->after('service_name')->constrained('hawans')->nullOnDelete();
            }

            if (!Schema::hasColumn('pandit_services', 'pooja_id')) {
                $table->foreignId('pooja_id')->nullable()->after('hawan_id')->constrained('poojas')->nullOnDelete();
            }
        });

        $hawans = collect();
        DB::table('hawans')->select(['id', 'name'])->orderBy('id')->each(function ($hawan) use ($hawans) {
            $this->nameKeys($hawan->name, ' Hawan')->each(fn ($key) => $hawans->put($key, $hawan->id));
        });

        $poojas = collect();
        DB::table('poojas')->select(['id', 'name'])->orderBy('id')->each(function ($pooja) use ($poojas) {
            $this->nameKeys($pooja->name, ' Pooja')->each(fn ($key) => $poojas->put($key, $pooja->id));
        });

        DB::table('pandit_services')
            ->select(['id', 'service_type', 'service_name'])
            ->orderBy('id')
            ->chunkById(100, function ($services) use ($hawans, $poojas) {
                foreach ($services as $service) {
                    $key = Str::of($service->service_name)->lower()->trim()->toString();

                    if ($service->service_type === 'hawan' && $hawans->has($key)) {
                        DB::table('pandit_services')->where('id', $service->id)->update(['hawan_id' => $hawans[$key]]);
                    }

                    if ($service->service_type === 'pooja' && $poojas->has($key)) {
                        DB::table('pandit_services')->where('id', $service->id)->update(['pooja_id' => $poojas[$key]]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('pandit_services', function (Blueprint $table) {
            if (Schema::hasColumn('pandit_services', 'pooja_id')) {
                $table->dropConstrainedForeignId('pooja_id');
            }

            if (Schema::hasColumn('pandit_services', 'hawan_id')) {
                $table->dropConstrainedForeignId('hawan_id');
            }
        });
    }

    private function nameKeys(string $name, string $suffix)
    {
        $name = trim($name);

        return collect([
            $name,
            Str::replaceLast($suffix, '', $name),
        ])
            ->map(fn ($value) => Str::of($value)->lower()->trim()->toString())
            ->filter()
            ->unique();
    }
};
