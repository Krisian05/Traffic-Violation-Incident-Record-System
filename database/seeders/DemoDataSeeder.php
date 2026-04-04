<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\IncidentChargeType;
use App\Models\IncidentMotorist;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Users ─────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['username' => 'officer1'],
            [
                'name'     => 'Juan dela Cruz',
                'role'     => 'traffic_officer',
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'admincop6043')),
            ]
        );

        User::updateOrCreate(
            ['username' => 'officer2'],
            [
                'name'     => 'Maria Santos',
                'role'     => 'traffic_officer',
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'admincop6043')),
            ]
        );

        // ── 2. Violators ─────────────────────────────────────────────────────
        $violatorsData = [
            [
                'first_name'          => 'Ricardo',
                'middle_name'         => 'Flores',
                'last_name'           => 'Reyes',
                'date_of_birth'       => '1985-03-14',
                'place_of_birth'      => 'Manila',
                'gender'              => 'Male',
                'civil_status'        => 'Married',
                'permanent_address'   => '12 Mabini St., Sampaloc, Manila',
                'contact_number'      => '09171234567',
                'email'               => 'ricardo.reyes@email.com',
                'license_number'      => 'N01-23-456789',
                'license_type'        => 'Non-Professional',
                'license_restriction' => '1,2',
                'license_issued_date' => '2021-06-01',
                'license_expiry_date' => '2026-06-01',
                'blood_type'          => 'O+',
                'height'              => 170,
                'weight'              => 72,
            ],
            [
                'first_name'          => 'Angelica',
                'middle_name'         => 'Cruz',
                'last_name'           => 'Villanueva',
                'date_of_birth'       => '1992-07-22',
                'place_of_birth'      => 'Quezon City',
                'gender'              => 'Female',
                'civil_status'        => 'Single',
                'permanent_address'   => '45 Katipunan Ave., Quezon City',
                'contact_number'      => '09281234567',
                'email'               => 'angelica.villanueva@email.com',
                'license_number'      => 'P02-24-112233',
                'license_type'        => 'Professional',
                'license_restriction' => '1,2,3',
                'license_issued_date' => '2022-01-15',
                'license_expiry_date' => '2027-01-15',
                'blood_type'          => 'A+',
                'height'              => 158,
                'weight'              => 55,
            ],
            [
                'first_name'          => 'Rodrigo',
                'middle_name'         => 'Bautista',
                'last_name'           => 'Mendoza',
                'date_of_birth'       => '1978-11-05',
                'place_of_birth'      => 'Cebu City',
                'gender'              => 'Male',
                'civil_status'        => 'Married',
                'permanent_address'   => '78 Osmeña Blvd., Cebu City',
                'contact_number'      => '09351234567',
                'email'               => 'rodrigo.mendoza@email.com',
                'license_number'      => 'N03-20-998877',
                'license_type'        => 'Non-Professional',
                'license_restriction' => '1',
                'license_issued_date' => '2020-03-10',
                'license_expiry_date' => '2025-03-10',
                'blood_type'          => 'B+',
                'height'              => 168,
                'weight'              => 80,
            ],
            [
                'first_name'          => 'Kristine',
                'middle_name'         => 'Ramos',
                'last_name'           => 'Garcia',
                'date_of_birth'       => '1995-02-18',
                'place_of_birth'      => 'Davao City',
                'gender'              => 'Female',
                'civil_status'        => 'Single',
                'permanent_address'   => '22 JP Laurel Ave., Davao City',
                'contact_number'      => '09461234567',
                'license_number'      => 'N04-23-554433',
                'license_type'        => 'Non-Professional',
                'license_restriction' => '1,2',
                'license_issued_date' => '2023-05-20',
                'license_expiry_date' => '2028-05-20',
                'blood_type'          => 'AB+',
                'height'              => 162,
                'weight'              => 58,
            ],
            [
                'first_name'          => 'Emmanuel',
                'middle_name'         => 'Torres',
                'last_name'           => 'Lim',
                'date_of_birth'       => '1980-09-30',
                'place_of_birth'      => 'Makati City',
                'gender'              => 'Male',
                'civil_status'        => 'Married',
                'permanent_address'   => '100 Ayala Ave., Makati City',
                'contact_number'      => '09571234567',
                'email'               => 'emmanuel.lim@email.com',
                'license_number'      => 'P05-21-667788',
                'license_type'        => 'Professional',
                'license_restriction' => '1,2,3,4',
                'license_issued_date' => '2021-08-12',
                'license_expiry_date' => '2026-08-12',
                'blood_type'          => 'O-',
                'height'              => 175,
                'weight'              => 85,
            ],
            [
                'first_name'          => 'Josephine',
                'middle_name'         => 'Dela',
                'last_name'           => 'Peña',
                'date_of_birth'       => '1988-12-03',
                'place_of_birth'      => 'Pasig City',
                'gender'              => 'Female',
                'civil_status'        => 'Married',
                'permanent_address'   => '5 Shaw Blvd., Pasig City',
                'contact_number'      => '09681234567',
                'license_number'      => 'N06-22-334455',
                'license_type'        => 'Non-Professional',
                'license_restriction' => '1,2',
                'license_issued_date' => '2022-11-08',
                'license_expiry_date' => '2027-11-08',
                'blood_type'          => 'A-',
                'height'              => 155,
                'weight'              => 52,
            ],
            [
                'first_name'          => 'Antonio',
                'middle_name'         => 'Navarro',
                'last_name'           => 'Castillo',
                'date_of_birth'       => '1975-04-17',
                'place_of_birth'      => 'Iloilo City',
                'gender'              => 'Male',
                'civil_status'        => 'Widowed',
                'permanent_address'   => '33 Iznart St., Iloilo City',
                'contact_number'      => '09791234567',
                'license_number'      => 'P07-19-889900',
                'license_type'        => 'Professional',
                'license_restriction' => '1,2,3',
                'license_issued_date' => '2019-02-25',
                'license_expiry_date' => '2024-02-25',
                'blood_type'          => 'B-',
                'height'              => 172,
                'weight'              => 78,
            ],
            [
                'first_name'          => 'Maricel',
                'middle_name'         => 'Aquino',
                'last_name'           => 'Fernandez',
                'date_of_birth'       => '2000-06-11',
                'place_of_birth'      => 'Taguig City',
                'gender'              => 'Female',
                'civil_status'        => 'Single',
                'permanent_address'   => '18 BGC, Taguig City',
                'contact_number'      => '09881234567',
                'license_number'      => 'N08-24-221100',
                'license_type'        => 'Non-Professional',
                'license_restriction' => '1',
                'license_issued_date' => '2024-01-10',
                'license_expiry_date' => '2029-01-10',
                'blood_type'          => 'O+',
                'height'              => 160,
                'weight'              => 50,
            ],
        ];

        $admin    = User::where('username', 'admin')->first();
        $officer1 = User::where('username', 'officer1')->first();
        $officer2 = User::where('username', 'officer2')->first();

        $violators = [];
        foreach ($violatorsData as $data) {
            $violators[] = Violator::firstOrCreate(
                ['license_number' => $data['license_number']],
                $data
            );
        }

        // ── 3. Vehicles ───────────────────────────────────────────────────────
        $vehiclesData = [
            ['violator' => 0, 'plate_number' => 'ABC 1234', 'vehicle_type' => 'MV', 'make' => 'Toyota',    'model' => 'Vios',      'color' => 'White',  'year' => 2020, 'or_number' => 'OR-001-2024', 'cr_number' => 'CR-001-2024', 'chassis_number' => 'TMK12345678', 'owner_name' => 'Ricardo Flores Reyes'],
            ['violator' => 0, 'plate_number' => 'XYZ 5678', 'vehicle_type' => 'MC', 'make' => 'Honda',     'model' => 'Click 125', 'color' => 'Black',  'year' => 2022, 'or_number' => 'OR-002-2024', 'cr_number' => 'CR-002-2024', 'chassis_number' => 'HND98765432', 'owner_name' => 'Ricardo Flores Reyes'],
            ['violator' => 1, 'plate_number' => 'DEF 4321', 'vehicle_type' => 'MV', 'make' => 'Ford',      'model' => 'EcoSport',  'color' => 'Gray',   'year' => 2021, 'or_number' => 'OR-003-2024', 'cr_number' => 'CR-003-2024', 'chassis_number' => 'FRD55544433', 'owner_name' => 'Angelica Cruz Villanueva'],
            ['violator' => 2, 'plate_number' => 'GHI 8765', 'vehicle_type' => 'MV', 'make' => 'Mitsubishi','model' => 'Strada',    'color' => 'Blue',   'year' => 2019, 'or_number' => 'OR-004-2024', 'cr_number' => 'CR-004-2024', 'chassis_number' => 'MBT11122233', 'owner_name' => 'Rodrigo Bautista Mendoza'],
            ['violator' => 4, 'plate_number' => 'JKL 2468', 'vehicle_type' => 'MV', 'make' => 'Toyota',    'model' => 'Hiace',     'color' => 'Silver', 'year' => 2018, 'or_number' => 'OR-005-2024', 'cr_number' => 'CR-005-2024', 'chassis_number' => 'TYT66677788', 'owner_name' => 'Emmanuel Torres Lim'],
            ['violator' => 6, 'plate_number' => 'MNO 1357', 'vehicle_type' => 'MV', 'make' => 'Nissan',    'model' => 'Almera',    'color' => 'Red',    'year' => 2023, 'or_number' => 'OR-006-2024', 'cr_number' => 'CR-006-2024', 'chassis_number' => 'NSN33322211', 'owner_name' => 'Antonio Navarro Castillo'],
            ['violator' => 7, 'plate_number' => 'PQR 9753', 'vehicle_type' => 'MC', 'make' => 'Yamaha',    'model' => 'Mio Aerox', 'color' => 'Yellow', 'year' => 2023, 'or_number' => 'OR-007-2024', 'cr_number' => 'CR-007-2024', 'chassis_number' => 'YMH88899900', 'owner_name' => 'Maricel Aquino Fernandez'],
        ];

        $vehicles = [];
        foreach ($vehiclesData as $vd) {
            $violatorIdx = $vd['violator'];
            unset($vd['violator']);
            $vehicles[] = Vehicle::firstOrCreate(
                ['plate_number' => $vd['plate_number']],
                ['violator_id' => $violators[$violatorIdx]->id] + $vd
            );
        }

        // ── 4. Violations ─────────────────────────────────────────────────────
        $types = ViolationType::pluck('id', 'name');

        $violationsData = [
            // Settled violations
            ['violator' => 0, 'vehicle' => 0, 'type' => 'Speeding',            'date' => now()->subDays(90), 'location' => 'EDSA, Quezon City',           'ticket' => 'TKT-2026-0001', 'status' => 'settled', 'notes' => 'Caught on radar at 82 kph in a 60 kph zone.', 'settled_at' => now()->subDays(85), 'or_number' => 'OR2026-0001', 'cashier_name' => 'Maria Santos'],
            ['violator' => 0, 'vehicle' => 1, 'type' => 'Beating Red Light',   'date' => now()->subDays(60), 'location' => 'España Blvd., Manila',         'ticket' => 'TKT-2026-0002', 'status' => 'settled', 'notes' => 'Ran red light at España-Lacson intersection.', 'settled_at' => now()->subDays(55), 'or_number' => 'OR2026-0002', 'cashier_name' => 'Juan dela Cruz'],
            ['violator' => 2, 'vehicle' => 3, 'type' => 'No Seatbelt',         'date' => now()->subDays(45), 'location' => 'C5 Road, Pasig City',          'ticket' => 'TKT-2026-0003', 'status' => 'settled', 'notes' => 'Driver and front passenger not wearing seatbelts.', 'settled_at' => now()->subDays(40), 'or_number' => 'OR2026-0003', 'cashier_name' => 'Maria Santos'],
            ['violator' => 4, 'vehicle' => 4, 'type' => 'Illegal Parking',     'date' => now()->subDays(30), 'location' => 'Ayala Ave., Makati City',      'ticket' => 'TKT-2026-0004', 'status' => 'settled', 'notes' => 'Parked in a no-parking zone near BPI main office.', 'settled_at' => now()->subDays(25), 'or_number' => 'OR2026-0004', 'cashier_name' => 'Juan dela Cruz'],
            ['violator' => 1, 'vehicle' => 2, 'type' => 'Counterflow',         'date' => now()->subDays(20), 'location' => 'Commonwealth Ave., QC',        'ticket' => 'TKT-2026-0005', 'status' => 'settled', 'notes' => 'Drove against traffic flow to avoid congestion.', 'settled_at' => now()->subDays(15), 'or_number' => 'OR2026-0005', 'cashier_name' => 'Maria Santos'],

            // Overdue (pending, >72 hours ago) — repeat offenders
            ['violator' => 0, 'vehicle' => 0, 'type' => 'Reckless Driving',    'date' => now()->subDays(10), 'location' => 'Roxas Blvd., Pasay City',      'ticket' => 'TKT-2026-0006', 'status' => 'pending', 'notes' => 'Weaving through traffic at high speed.'],
            ['violator' => 0, 'vehicle' => 1, 'type' => 'Speeding',            'date' => now()->subDays(8),  'location' => 'McArthur Highway, Caloocan',   'ticket' => 'TKT-2026-0007', 'status' => 'pending', 'notes' => 'Clocked at 95 kph in 60 kph zone.'],
            ['violator' => 2, 'vehicle' => 3, 'type' => 'Illegal U-Turn',      'date' => now()->subDays(7),  'location' => 'Buendia Ave., Makati',         'ticket' => 'TKT-2026-0008', 'status' => 'pending', 'notes' => 'Made U-turn in a no-U-turn zone.'],
            ['violator' => 2, 'vehicle' => 3, 'type' => 'No Driver\'s License','date' => now()->subDays(5),  'location' => 'Ortigas Ave., Mandaluyong',    'ticket' => 'TKT-2026-0009', 'status' => 'pending', 'notes' => 'Unable to present driver\'s license when flagged.'],
            ['violator' => 6, 'vehicle' => 5, 'type' => 'Distracted Driving',  'date' => now()->subDays(6),  'location' => 'Quezon Ave., Quezon City',     'ticket' => 'TKT-2026-0010', 'status' => 'pending', 'notes' => 'Using mobile phone while driving.'],
            ['violator' => 4, 'vehicle' => 4, 'type' => 'Overloading (Passengers)', 'date' => now()->subDays(4), 'location' => 'EDSA, Cubao',             'ticket' => 'TKT-2026-0011', 'status' => 'pending', 'notes' => 'Van carrying 18 passengers, capacity is 12.'],
            ['violator' => 7, 'vehicle' => 6, 'type' => 'No Helmet',           'date' => now()->subDays(3),  'location' => 'Aurora Blvd., Cubao',         'ticket' => 'TKT-2026-0012', 'status' => 'pending', 'notes' => 'Motorcycle rider without helmet.'],

            // Recent pending (within 72 hours)
            ['violator' => 3, 'vehicle' => null, 'type' => 'No Vehicle Registration', 'date' => now()->subHours(6), 'location' => 'Taft Ave., Manila', 'ticket' => 'TKT-2026-0013', 'status' => 'pending', 'notes' => 'No valid OR/CR presented.'],
            ['violator' => 5, 'vehicle' => null, 'type' => 'Illegal Parking',  'date' => now()->subHours(3),  'location' => 'Shaw Blvd., Mandaluyong',   'ticket' => 'TKT-2026-0014', 'status' => 'pending', 'notes' => 'Double parked blocking traffic.'],
            ['violator' => 1, 'vehicle' => 2,    'type' => 'Beating Red Light', 'date' => now()->subHours(2), 'location' => 'Katipunan Ave., QC',         'ticket' => 'TKT-2026-0015', 'status' => 'pending', 'notes' => 'Caught on CCTV running red light.'],
        ];

        foreach ($violationsData as $vd) {
            $typeId = $types[$vd['type']] ?? $types->first();
            Violation::firstOrCreate(
                ['ticket_number' => $vd['ticket']],
                [
                    'violator_id'       => $violators[$vd['violator']]->id,
                    'vehicle_id'        => isset($vd['vehicle']) && $vd['vehicle'] !== null ? $vehicles[$vd['vehicle']]->id : null,
                    'violation_type_id' => $typeId,
                    'date_of_violation' => $vd['date'],
                    'location'          => $vd['location'],
                    'ticket_number'     => $vd['ticket'],
                    'status'            => $vd['status'],
                    'notes'             => $vd['notes'],
                    'recorded_by'       => $officer1->id,
                    'settled_at'        => $vd['settled_at'] ?? null,
                    'or_number'         => $vd['or_number'] ?? null,
                    'cashier_name'      => $vd['cashier_name'] ?? null,
                ]
            );
        }

        // ── 5. Incidents ──────────────────────────────────────────────────────
        $chargeTypes = IncidentChargeType::pluck('id', 'name');

        $incidentsData = [
            [
                'date'        => now()->subDays(15),
                'time'        => '14:30:00',
                'location'    => 'EDSA cor. Ortigas Ave., Mandaluyong City',
                'description' => 'Two-vehicle collision involving a sedan and a motorcycle. Sedan ran a red light causing the motorcycle rider to swerve and fall. Rider sustained minor injuries. Both vehicles have moderate damage.',
                'status'      => 'closed',
                'motorists'   => [
                    [
                        'violator'   => 0,
                        'vehicle'    => 0,
                        'charge'     => 'Reckless Imprudence Resulting in Slight Physical Injuries',
                        'notes'      => 'Driver ran red light. Primary at fault.',
                    ],
                    [
                        'violator'   => 7,
                        'vehicle'    => 6,
                        'charge'     => 'Violation of Land Transportation and Traffic Code (RA 4136)',
                        'notes'      => 'Motorcycle rider. Victim. No helmet at time of incident.',
                    ],
                ],
            ],
            [
                'date'        => now()->subDays(7),
                'time'        => '08:15:00',
                'location'    => 'C5 Road cor. Kalayaan Ave., Makati City',
                'description' => 'Hit-and-run incident. A pickup truck sideswiped a parked vehicle and fled the scene. Witness provided partial plate number. Suspect vehicle later identified and apprehended.',
                'status'      => 'under_review',
                'motorists'   => [
                    [
                        'violator'   => 2,
                        'vehicle'    => 3,
                        'charge'     => 'Hit and Run',
                        'notes'      => 'Driver of pickup truck. Fled scene. Apprehended 2 hours later.',
                    ],
                ],
            ],
            [
                'date'        => now()->subDays(3),
                'time'        => '22:45:00',
                'location'    => 'Roxas Blvd., Pasay City',
                'description' => 'Multi-vehicle collision involving three vehicles. Drunk driver rear-ended the first vehicle causing a chain reaction. Two persons injured, one seriously. Road was temporarily closed for 2 hours.',
                'status'      => 'open',
                'motorists'   => [
                    [
                        'violator'   => 6,
                        'vehicle'    => 5,
                        'charge'     => 'Driving Under the Influence (RA 10586)',
                        'notes'      => 'Primary at fault. BAC was 0.08%. Under arrest.',
                    ],
                    [
                        'violator'   => 4,
                        'vehicle'    => 4,
                        'charge'     => 'Reckless Imprudence Resulting in Serious Physical Injuries',
                        'notes'      => 'Second vehicle in chain collision. Victim.',
                    ],
                ],
            ],
            [
                'date'        => now()->subDays(1),
                'time'        => '07:30:00',
                'location'    => 'Commonwealth Ave., Quezon City',
                'description' => 'Motorcycle vs. jeepney sideswipe incident. Motorcycle rider was overtaking on the wrong side when a jeepney merged into the lane. Minor injuries to the motorcycle rider.',
                'status'      => 'open',
                'motorists'   => [
                    [
                        'violator'   => 7,
                        'vehicle'    => 6,
                        'charge'     => 'Reckless Imprudence Resulting in Slight Physical Injuries',
                        'notes'      => 'Motorcycle rider. Overtaking on wrong side.',
                    ],
                ],
            ],
            [
                'date'        => now()->subHours(5),
                'time'        => '11:00:00',
                'location'    => 'España Blvd. cor. Lacson Ave., Manila',
                'description' => 'Pedestrian struck by a vehicle while crossing at a non-pedestrian crossing. Driver claims the pedestrian suddenly appeared from between parked vehicles. Pedestrian rushed to UST Hospital.',
                'status'      => 'open',
                'motorists'   => [
                    [
                        'violator'   => 3,
                        'vehicle'    => null,
                        'charge'     => 'Reckless Imprudence Resulting in Serious Physical Injuries',
                        'notes'      => 'Driver of vehicle that struck pedestrian. No prior violations.',
                    ],
                ],
            ],
        ];

        foreach ($incidentsData as $inc) {
            $existing = Incident::whereDate('date_of_incident', $inc['date'])->where('location', $inc['location'])->first();
            if ($existing) continue;

            $incident = Incident::create([
                'date_of_incident'  => $inc['date'],
                'time_of_incident'  => $inc['time'],
                'location'          => $inc['location'],
                'description'       => $inc['description'],
                'status'            => $inc['status'],
                'recorded_by'       => $admin->id,
            ]);

            foreach ($inc['motorists'] as $m) {
                $violator = $violators[$m['violator']];
                $vehicle  = isset($m['vehicle']) && $m['vehicle'] !== null ? $vehicles[$m['vehicle']] : null;
                $chargeId = $chargeTypes[$m['charge']] ?? $chargeTypes->first();

                IncidentMotorist::create([
                    'incident_id'             => $incident->id,
                    'violator_id'             => $violator->id,
                    'motorist_name'           => $violator->full_name,
                    'motorist_license'        => $violator->license_number,
                    'motorist_contact'        => $violator->contact_number,
                    'motorist_address'        => $violator->permanent_address,
                    'license_type'            => $violator->license_type,
                    'license_restriction'     => $violator->license_restriction,
                    'license_expiry_date'     => $violator->license_expiry_date,
                    'vehicle_id'              => $vehicle?->id,
                    'vehicle_plate'           => $vehicle?->plate_number,
                    'vehicle_make'            => $vehicle?->make,
                    'vehicle_model'           => $vehicle?->model,
                    'vehicle_color'           => $vehicle?->color,
                    'vehicle_or_number'       => $vehicle?->or_number,
                    'vehicle_cr_number'       => $vehicle?->cr_number,
                    'incident_charge_type_id' => $chargeId,
                    'notes'                   => $m['notes'],
                ]);
            }
        }
    }
}
