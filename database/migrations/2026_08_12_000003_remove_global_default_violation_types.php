<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Lgu;
use App\Models\Violation;
use App\Models\ViolationType;

return new class extends Migration
{
    public function up(): void
    {
        $globalTypes = ViolationType::whereNull('lgu_id')->get();
        $firstLguId = Lgu::first()?->id;

        foreach ($globalTypes as $gType) {
            $violations = Violation::where('violation_type_id', $gType->id)->get();
            foreach ($violations as $viol) {
                $targetLguId = $viol->lgu_id ?: $firstLguId;
                if ($targetLguId) {
                    $lguType = ViolationType::where('lgu_id', $targetLguId)
                        ->where('name', $gType->name)
                        ->first();

                    if (!$lguType) {
                        $lguType = ViolationType::create([
                            'lgu_id'              => $targetLguId,
                            'name'                => $gType->name,
                            'code'                => $gType->code,
                            'description'         => $gType->description,
                            'fine_amount'         => $gType->fine_amount,
                            'late_penalty_amount' => $gType->late_penalty_amount,
                            'points'              => $gType->points,
                        ]);
                    }

                    $viol->update(['violation_type_id' => $lguType->id]);
                }
            }
        }

        // Delete all global default types now that no violations reference them
        ViolationType::whereNull('lgu_id')->delete();
    }

    public function down(): void
    {
        // No roll back
    }
};
