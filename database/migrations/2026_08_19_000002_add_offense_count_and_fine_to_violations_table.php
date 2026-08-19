<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->unsignedTinyInteger('offense_count')->default(1)->after('violation_type_id');
            $table->decimal('fine_amount', 10, 2)->nullable()->after('offense_count');
        });

        // Backfill chronological offense counts and fine amounts for existing violations
        DB::transaction(function () {
            $violators = DB::table('violations')
                ->select('violator_id')
                ->distinct()
                ->pluck('violator_id');

            foreach ($violators as $violatorId) {
                $violations = DB::table('violations')
                    ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                    ->where('violations.violator_id', $violatorId)
                    ->orderBy('violations.date_of_violation', 'asc')
                    ->orderBy('violations.id', 'asc')
                    ->select(
                        'violations.id',
                        'violations.violation_type_id',
                        'violation_types.fine_amount as base_fine',
                        'violation_types.fine_amount_2nd',
                        'violation_types.fine_amount_3rd'
                    )
                    ->get();

                $typeCounts = [];
                foreach ($violations as $v) {
                    $typeId = $v->violation_type_id;
                    $typeCounts[$typeId] = ($typeCounts[$typeId] ?? 0) + 1;
                    $count = $typeCounts[$typeId];

                    // Calculate tiered fine
                    $fine = $v->base_fine ?? 0.00;
                    if ($count === 2 && !is_null($v->fine_amount_2nd)) {
                        $fine = $v->fine_amount_2nd;
                    } elseif ($count >= 3 && (!is_null($v->fine_amount_3rd) || !is_null($v->fine_amount_2nd))) {
                        $fine = $v->fine_amount_3rd ?? $v->fine_amount_2nd ?? $v->base_fine;
                    }

                    DB::table('violations')
                        ->where('id', $v->id)
                        ->update([
                            'offense_count' => $count,
                            'fine_amount'   => $fine,
                        ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn(['offense_count', 'fine_amount']);
        });
    }
};
