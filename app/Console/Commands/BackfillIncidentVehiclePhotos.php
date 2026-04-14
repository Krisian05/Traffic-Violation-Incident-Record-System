<?php

namespace App\Console\Commands;

use App\Models\IncidentMotorist;
use App\Models\VehiclePhoto;
use Illuminate\Console\Command;

class BackfillIncidentVehiclePhotos extends Command
{
    protected $signature   = 'tvrs:backfill-vehicle-photos';
    protected $description = 'Sync vehicle photos from incident_motorists.vehicle_photo into the vehicle_photos table';

    public function handle(): int
    {
        $rows = IncidentMotorist::whereNotNull('vehicle_id')
            ->whereNotNull('vehicle_photo')
            ->get(['id', 'vehicle_id', 'vehicle_photo']);

        $inserted = 0;

        foreach ($rows as $motorist) {
            $photos = is_array($motorist->vehicle_photo) ? $motorist->vehicle_photo : [];

            foreach ($photos as $path) {
                if (!$path) continue;

                $already = VehiclePhoto::where('vehicle_id', $motorist->vehicle_id)
                    ->where('photo', $path)
                    ->exists();

                if (!$already) {
                    VehiclePhoto::create(['vehicle_id' => $motorist->vehicle_id, 'photo' => $path]);
                    $inserted++;
                }
            }
        }

        $this->info("Done. Inserted {$inserted} missing vehicle photo records.");
        return 0;
    }
}
