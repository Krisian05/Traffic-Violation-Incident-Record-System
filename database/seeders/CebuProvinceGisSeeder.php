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
            'BAL' => ['name' => 'Balamban', 'code' => 'BAL', 'province' => 'Cebu', 'psgc_city_code' => '072208000', 'police_station_name' => 'BALAMBAN MUNICIPAL POLICE STATION', 'police_station_address' => 'Brgy. Sta Cruz-Sto Nino, Balamban, Cebu'],
            'CEB' => ['name' => 'Cebu City', 'code' => 'CEB', 'province' => 'Cebu', 'psgc_city_code' => '072217000', 'police_station_name' => 'CEBU CITY POLICE STATION', 'police_station_address' => 'Gorordo Ave, Cebu City, Cebu'],
            'MAN' => ['name' => 'Mandaue City', 'code' => 'MAN', 'province' => 'Cebu', 'psgc_city_code' => '072230000', 'police_station_name' => 'MANDAUE CITY POLICE STATION', 'police_station_address' => 'MC Briones St, Mandaue City, Cebu'],
            'DAN' => ['name' => 'Danao City', 'code' => 'DAN', 'province' => 'Cebu', 'psgc_city_code' => '072223000', 'police_station_name' => 'DANAO CITY POLICE STATION', 'police_station_address' => 'F. Ralota St, Danao City, Cebu'],
            'CAR' => ['name' => 'Carcar City', 'code' => 'CAR', 'province' => 'Cebu', 'psgc_city_code' => '072214000', 'police_station_name' => 'CARCAR CITY POLICE STATION', 'police_station_address' => 'Poblacion 1, Carcar City, Cebu'],
            'TAL' => ['name' => 'Talisay City', 'code' => 'TAL', 'province' => 'Cebu', 'psgc_city_code' => '072250000', 'police_station_name' => 'TALISAY CITY POLICE STATION', 'police_station_address' => 'Poblacion, Talisay City, Cebu'],
            'TOL' => ['name' => 'Toledo City', 'code' => 'TOL', 'province' => 'Cebu', 'psgc_city_code' => '072252000', 'police_station_name' => 'TOLEDO CITY POLICE STATION', 'police_station_address' => 'Poblacion, Toledo City, Cebu'],
        ];

        $createdLgus = [];
        foreach ($lgus as $code => $data) {
            $lguObj = Lgu::where('code', $code)->first();
            if ($lguObj) {
                $lguObj->update($data);
                $createdLgus[$code] = $lguObj;
            } else {
                $createdLgus[$code] = Lgu::create($data);
            }
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

        // 5. Exact Road & Intersection GPS Coordinates across Cebu Province
        $cebuGisLocations = [
            // Balamban Hotspots — verified via Mapcarta/OSM
            [
                'location' => 'Balamban Public Market, Transcentral Highway',
                'lat' => 10.5043,
                'lng' => 123.7136,
                'lgu' => $createdLgus['BAL'],
                'count' => 5,
            ],
            [
                // Nivel Hills is on the Cebu City side of the Transcentral Hwy, near Lahug
                'location' => 'Nivel Hills, Transcentral Highway, Cebu City',
                'lat' => 10.3390,
                'lng' => 123.8860,
                'lgu' => $createdLgus['CEB'],
                'count' => 4,
            ],
            // Mandaue City Hotspots — verified via Wikimapia (10°19'22"N = 10.3228, 123°55'28"E = 123.9244)
            [
                'location' => 'Subangdaku Flyover, M.C. Briones St, Mandaue City',
                'lat' => 10.3228,
                'lng' => 123.9244,
                'lgu' => $createdLgus['MAN'],
                'count' => 6,
            ],
            [
                'location' => 'UN Avenue & M.C. Briones Junction, Mandaue City',
                'lat' => 10.3312,
                'lng' => 123.9352,
                'lgu' => $createdLgus['MAN'],
                'count' => 4,
            ],
            // Cebu City Hotspots — verified via Wikipedia & Facebook
            [
                'location' => 'SRP Coastal Road, South Road Properties, Cebu City',
                'lat' => 10.2757,
                'lng' => 123.8776,
                'lgu' => $createdLgus['CEB'],
                'count' => 7,
            ],
            [
                'location' => 'Fuente Osmeña Circle, Cebu City',
                'lat' => 10.3093,
                'lng' => 123.8924,
                'lgu' => $createdLgus['CEB'],
                'count' => 5,
            ],
            // Danao City Hotspots — verified via Discover PH (10.52047, 124.03014)
            [
                'location' => 'Danao City Port Highway, Danao City',
                'lat' => 10.5205,
                'lng' => 124.0301,
                'lgu' => $createdLgus['DAN'],
                'count' => 4,
            ],
            // Carcar City Hotspots — verified via Google/Trip.com (10.0988, 123.6440)
            [
                'location' => 'Carcar Rotunda Highway, Carcar City',
                'lat' => 10.0988,
                'lng' => 123.6440,
                'lgu' => $createdLgus['CAR'],
                'count' => 4,
            ],
            // Talisay City Hotspots — verified via Mapcarta (10.26358, 123.83982)
            [
                'location' => 'Tabunok Flyover, Natalio Bacalso Ave, Talisay City',
                'lat' => 10.2636,
                'lng' => 123.8398,
                'lgu' => $createdLgus['TAL'],
                'count' => 5,
            ],
            // Toledo City Hotspots — verified via Wikipedia (10.3833, 123.6333)
            [
                'location' => 'Toledo City Port Highway, Toledo City',
                'lat' => 10.3833,
                'lng' => 123.6333,
                'lgu' => $createdLgus['TOL'],
                'count' => 3,
            ],
        ];


        // Clear previous test GIS entries to ensure exact pin placement
        Violation::where('ticket_number', 'LIKE', 'TVIRS-GIS-%')->forceDelete();
        Incident::where('incident_number', 'LIKE', 'INC-GIS-%')->forceDelete();

        // Seed Violations & Incidents at 100% exact intersection/road coordinates (zero jitter)
        foreach ($cebuGisLocations as $idx => $locData) {
            for ($i = 0; $i < $locData['count']; $i++) {
                $exactLat = $locData['lat'];
                $exactLng = $locData['lng'];

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
                        'status' => rand(0, 1) ? 'assigned_for_investigation' : 'resolved',
                        'description' => 'Traffic incident reported at ' . $locData['location'],
                    ]);
                }
            }
        }
    }
}
