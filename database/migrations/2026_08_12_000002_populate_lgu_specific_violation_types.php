<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Lgu;
use App\Models\ViolationType;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::getConnection()->getDriverName() === 'pgsql') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE violation_types DROP CONSTRAINT IF EXISTS uq_violation_types_name');
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE violation_types DROP CONSTRAINT IF EXISTS violation_types_name_unique');
            }
        } catch (\Throwable $e) {}

        $globalTypes = ViolationType::whereNull('lgu_id')->get();
        if ($globalTypes->isEmpty()) {
            return;
        }

        $lgus = Lgu::all();
        foreach ($lgus as $lgu) {
            $existingCount = ViolationType::where('lgu_id', $lgu->id)->count();
            if ($existingCount === 0) {
                foreach ($globalTypes as $type) {
                    ViolationType::create([
                        'lgu_id'              => $lgu->id,
                        'name'                => $type->name,
                        'code'                => $type->code,
                        'description'         => $type->description,
                        'fine_amount'         => $type->fine_amount,
                        'late_penalty_amount' => $type->late_penalty_amount,
                        'points'              => $type->points,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No destruct on rollback
    }
};
