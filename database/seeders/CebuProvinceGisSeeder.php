<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Database\Seeder;

class CebuProvinceGisSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure LGUs across Cebu Province exist
        $lgus = [
            'BAL' => ['name' => 'Balamban', 'code' => 'BAL', 'province' => 'Cebu', 'psgc_city_code' => '072208000'],
            'CEB' => ['name' => 'Cebu City', 'code' => 'CEB', 'province' => 'Cebu', 'psgc_city_code' => '072217000'],
            'MAN' => ['name' => 'Mandaue City', 'code' => 'MAN', 'province' => 'Cebu', 'psgc_city_code' => '072230000'],
            'DAN' => ['name' => 'Danao City', 'code' => 'DAN', 'province' => 'Cebu', 'psgc_city_code' => '072223000'],
            'CAR' => ['name' => 'Carcar City', 'code' => 'CAR', 'province' => 'Cebu', 'psgc_city_code' => '072214000'],
            'TAL' => ['name' => 'Talisay City', 'code' => 'TAL', 'province' => 'Cebu', 'psgc_city_code' => '072250000'],
            'TOL' => ['name' => 'Toledo City', 'code' => 'TOL', 'province' => 'Cebu', 'psgc_city_code' => '072252000'],
        ];

        $createdLgus = [];
        foreach ($lgus as $code => $data) {
            $createdLgus[$code] = Lgu::firstOrCreate(['code' => $code], $data);
        }

        // 2. Ensure an admin/recorder user exists
        $user = User::first() ?? User::create([
            'name' => 'Admin Officer',
            'username' => 'admin_gis',
            'email' => 'admin_gis@cebu.gov.ph',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        // 3. Ensure Violation Types exist
        $vTypes = ViolationType::all();
        if ($vTypes->isEmpty()) {
            $vType = ViolationType::create([
                'name' => 'Reckless Driving',
                'fine_amount' => 1500.00,
                'code' => 'RD-01',
            ]);
        } else {
            $vType = $vTypes->first();
        }

        // 4. Ensure Violators exist
        $violators = Violator::all();
        if ($violators->isEmpty()) {
            $violator = Violator::create([
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'license_number' => 'N01-18-998877',
                'lgu_id' => $createdLgus['BAL']->id,
            ]);
        } else {
            $violator = $violators->first();
        }

        // 5. Hotspot & GPS Coordinates across Cebu Province
        $cebuGisLocations = [
            // Balamban Hotspots
            [
                'location' => 'Balamban Public Market, Transcentral Highway',
                'lat' => 10.5050,
                'lng' => 123.7198,
                'lgu' => $createdLgus['BAL'],
                'count' => 5,
            ],
            [
                'location' => 'Nivel Hills, Transcentral Highway, Balamban',
                'lat' => 10.3700,
                'lng' => 123.8680,
                'lgu' => $createdLgus['BAL'],
                'count' => 4,
            ],
            // Mandaue City Hotspots
            [
                'location' => 'Subangdaku Flyover, Mandaue City',
                'lat' => 10.3275,
                'lng' => 123.9240,
                'lgu' => $createdLgus['MAN'],
                'count' => 6,
            ],
            [
                'location' => 'UN Avenue Corridor, Mandaue City',
                'lat' => 10.3395,
                'lng' => 123.9390,
                'lgu' => $createdLgus['MAN'],
                'count' => 3,
            ],
            // Cebu City Hotspots
            [
                'location' => 'SRP Coastal Road, South Road Properties, Cebu City',
                'lat' => 10.2850,
                'lng' => 123.8750,
                'lgu' => $createdLgus['CEB'],
                'count' => 7,
            ],
            [
                'location' => 'Fuente Osmeña Circle, Cebu City',
                'lat' => 10.3117,
                'lng' => 123.8917,
                'lgu' => $createdLgus['CEB'],
                'count' => 5,
            ],
            // Danao City Hotspots
            [
                'location' => 'Danao City Port Highway, Danao City',
                'lat' => 10.5255,
                'lng' => 124.0270,
                'lgu' => $createdLgus['DAN'],
                'count' => 4,
            ],
            // Carcar City Hotspots
            [
                'location' => 'Carcar Rotunda Highway, Carcar City',
                'lat' => 10.1060,
                'lng' => 123.6390,
                'lgu' => $createdLgus['CAR'],
                'count' => 4,
            ],
            // Talisay City Hotspots
            [
                'location' => 'Tabunok Flyover, CSCR Expressway, Talisay City',
                'lat' => 10.2720,
                'lng' => 123.8420,
                'lgu' => $createdLgus['TAL'],
                'count' => 5,
            ],
            // Toledo City Hotspots
            [
                'location' => 'Toledo City Port Highway, Toledo City',
                'lat' => 10.3780,
                'lng' => 123.6370,
                'lgu' => $createdLgus['TOL'],
                'count' => 3,
            ],
        ];

        // Seed Violations & Incidents at exact location coordinates
        foreach ($cebuGisLocations as $idx => $locData) {
            for ($i = 0; $i < $locData['count']; $i++) {
                // Exact location for primary point, micro offset (±0.00015) for overlapping pins
                $exactLat = $i === 0 ? $locData['lat'] : ($locData['lat'] + ((rand(-15, 15) / 100000)));
                $exactLng = $i === 0 ? $locData['lng'] : ($locData['lng'] + ((rand(-15, 15) / 100000)));

                if ($i % 2 === 0) {
                    // Seed Violation
                    Violation::create([
                        'violator_id' => $violator->id,
                        'violation_type_id' => $vTypes->random()->id ?? $vType->id,
                        'lgu_id' => $locData['lgu']->id,
                        'recorded_by' => $user->id,
                        'ticket_number' => 'TVIRS-GIS-' . strtoupper($locData['lgu']->code) . '-' . sprintf('%04d', rand(100, 9999)),
                        'date_of_violation' => now()->subDays(rand(1, 180))->toDateString(),
                        'location' => $locData['location'],
                        'gps_lat' => $exactLat,
                        'gps_lng' => $exactLng,
                        'status' => rand(0, 1) ? 'settled' : 'pending',
                    ]);
                } else {
                    // Seed Incident
                    Incident::create([
                        'lgu_id' => $locData['lgu']->id,
                        'recorded_by' => $user->id,
                        'incident_number' => 'INC-GIS-' . strtoupper($locData['lgu']->code) . '-' . sprintf('%04d', rand(100, 9999)),
                        'date_of_incident' => now()->subDays(rand(1, 180))->toDateTimeString(),
                        'location' => $locData['location'],
                        'gps_lat' => $exactLat,
                        'gps_lng' => $exactLng,
                        'status' => rand(0, 1) ? 'under_investigation' : 'solved',
                        'description' => 'Traffic incident reported at ' . $locData['location'],
                    ]);
                }
            }
        }
    }
}
