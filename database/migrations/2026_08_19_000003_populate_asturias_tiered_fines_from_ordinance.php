<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Lgu;
use App\Models\ViolationType;

return new class extends Migration
{
    public function up(): void
    {
        $asturias = Lgu::where('name', 'like', '%Asturias%')->first();
        if (!$asturias) {
            return;
        }

        $asturiasLguId = $asturias->id;

        $tieredOrdinances = [
            'Traffic Control Devices' => [
                'fine_amount'     => 1000.00,
                'fine_amount_2nd' => 2000.00,
                'fine_amount_3rd' => 2500.00,
            ],
            'Interference with Official Traffic Controls Devices, Signs' => [
                'fine_amount'     => 1000.00,
                'fine_amount_2nd' => 2000.00,
                'fine_amount_3rd' => 2500.00,
            ],
            'Regulating the Use of Public Roads, Sidewalks, Alleys, or Lanes' => [
                'fine_amount'     => 0.00, // 1st violation: warning
                'fine_amount_2nd' => 500.00,
                'fine_amount_3rd' => 500.00,
            ],
            'Abandoned Motor Vehicles/Trailers' => [
                'fine_amount'     => 200.00,
                'fine_amount_2nd' => 300.00,
                'fine_amount_3rd' => 500.00,
            ],
            'Unattended Motor Vehicle' => [
                'fine_amount'     => 200.00,
                'fine_amount_2nd' => 300.00,
                'fine_amount_3rd' => 500.00,
            ],
            'Bicycle Registration' => [
                'fine_amount'     => 10.00,
                'fine_amount_2nd' => 20.00,
                'fine_amount_3rd' => 30.00,
            ],
            'Excessive Motorized Tricycle Fare' => [
                'fine_amount'     => 200.00,
                'fine_amount_2nd' => 500.00,
                'fine_amount_3rd' => 1000.00,
            ],
        ];

        DB::transaction(function () use ($asturiasLguId, $tieredOrdinances) {
            foreach ($tieredOrdinances as $name => $fines) {
                ViolationType::where('lgu_id', $asturiasLguId)
                    ->where('name', $name)
                    ->update([
                        'fine_amount'     => $fines['fine_amount'],
                        'fine_amount_2nd' => $fines['fine_amount_2nd'],
                        'fine_amount_3rd' => $fines['fine_amount_3rd'],
                    ]);
            }

            Cache::forget('violation_types');
            Cache::forget("violation_types_lgu_{$asturiasLguId}");
        });
    }

    public function down(): void
    {
        // No destructive rollback needed
    }
};
