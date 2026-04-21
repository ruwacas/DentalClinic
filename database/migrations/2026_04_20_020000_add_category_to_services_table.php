<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->default('General')->after('id');
        });

        $serviceMap = collect(config('services_menu', []))
            ->flatMap(function (array $group) {
                $category = $group['category'] ?? 'General';

                return collect($group['services'] ?? [])->mapWithKeys(function (array $service) use ($category) {
                    $name = $service['name'] ?? null;

                    if (! $name) {
                        return [];
                    }

                    return [$name => $category];
                });
            });

        foreach ($serviceMap as $name => $category) {
            DB::table('services')->where('name', $name)->update(['category' => $category]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
