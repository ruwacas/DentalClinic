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
        $duplicateGroups = DB::table('availabilities')
            ->select('dentist_id', 'day_of_week', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->groupBy('dentist_id', 'day_of_week')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicateGroups as $group) {
            DB::table('availabilities')
                ->where('dentist_id', $group->dentist_id)
                ->where('day_of_week', $group->day_of_week)
                ->where('id', '!=', $group->keep_id)
                ->delete();
        }

        Schema::table('availabilities', function (Blueprint $table) {
            $table->unique(['dentist_id', 'day_of_week'], 'availabilities_dentist_day_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropUnique('availabilities_dentist_day_unique');
        });
    }
};
