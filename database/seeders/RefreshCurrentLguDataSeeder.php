<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\IncidentChargeType;
use App\Models\IncidentMotorist;
use App\Models\Lgu;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefreshCurrentLguDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Clear existing violation, motorist, incident, and payment records ──
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE payments, violation_vehicle_photos, violations, incident_motorists, incidents, vehicle_photos, vehicles, violators RESTART IDENTITY CASCADE;');
            if (DB::getSchemaBuilder()->hasTable('activity_log')) {
                DB::statement('TRUNCATE TABLE activity_log RESTART IDENTITY CASCADE;');
            }
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Payment::truncate();
            DB::table('violation_vehicle_photos')->truncate();
            Violation::truncate();
            IncidentMotorist::truncate();
            Incident::truncate();
            DB::table('vehicle_photos')->truncate();
            Vehicle::truncate();
            Violator::truncate();

            if (DB::getSchemaBuilder()->hasTable('activity_log')) {
                DB::table('activity_log')->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // ── 2. Get current LGUs ──
        $lgus = Lgu::all();
        if ($lgus->isEmpty()) {
            $lgus = collect([
                Lgu::create(['name' => 'Balamban', 'code' => 'BAL', 'province' => 'Cebu', 'psgc_city_code' => '072208000']),
                Lgu::create(['name' => 'Cebu City', 'code' => 'CEB', 'province' => 'Cebu', 'psgc_city_code' => '072217000']),
                Lgu::create(['name' => 'Mandaue City', 'code' => 'MAN', 'province' => 'Cebu', 'psgc_city_code' => '072230000']),
            ]);
        }

        // Recorder user
        $recorder = User::whereIn('role', ['admin', 'operator', 'records_officer'])->first()
            ?? User::first()
            ?? User::create([
                'name' => 'System Operator',
                'username' => 'system_op',
                'role' => 'admin',
                'password' => bcrypt('password'),
            ]);

        // Cashier user
        $cashier = User::where('role', 'cashier')->first() ?? $recorder;

        // Fetch violation types and charge types
        $vTypes = ViolationType::all();
        if ($vTypes->isEmpty()) {
            $this->call(DatabaseSeeder::class);
            $vTypes = ViolationType::all();
        }
        $cTypes = IncidentChargeType::all();

        // ── 3. Motorist dataset templates for LGUs ──
        $firstNames = ['Ricardo', 'Angelica', 'Rodrigo', 'Kristine', 'Emmanuel', 'Josephine', 'Marco', 'Teresa', 'Gabriel', 'Patricia', 'Fernando', 'Clarissa', 'Eduardo', 'Beatrice', 'Leonardo'];
        $middleNames = ['Flores', 'Cruz', 'Bautista', 'Ramos', 'Torres', 'Dela', 'Santos', 'Navarro', 'Garcia', 'Mendoza', 'Aquino', 'Reyes', 'Gonzales', 'Villanueva', 'Castillo'];
        $lastNames = ['Reyes', 'Villanueva', 'Mendoza', 'Garcia', 'Lim', 'Peña', 'Valdez', 'Castro', 'Delos Santos', 'Dizon', 'Soriano', 'Mercado', 'Ramos', 'Aguilar', 'Pineda'];

        $streetNames = ['Poblacion Main St.', 'National Highway', 'Mabini St.', 'Rizal Avenue', 'Osmeña Street', 'Burgos St.', 'Zamora St.', 'Luna Extension', 'Market Road', 'Coastal Highway'];

        $createdViolators = collect();
        $createdVehicles  = collect();

        $licenseTypes = ['Non-Professional', 'Professional'];
        $restrictions = ['1,2', '1,2,3', '1', '1,2,3,4', '2'];
        $bloodTypes   = ['O+', 'A+', 'B+', 'AB+', 'O-'];

        $ticketCounter = 1;
        $incidentCounter = 1;

        // ── Seed Motorists & Vehicles per current LGU ──
        foreach ($lgus as $lgu) {
            $motoristCount = rand(3, 4);

            for ($m = 0; $m < $motoristCount; $m++) {
                $fn = $firstNames[array_rand($firstNames)];
                $mn = $middleNames[array_rand($middleNames)];
                $ln = $lastNames[array_rand($lastNames)];
                $street = $streetNames[array_rand($streetNames)];

                $licNum = sprintf('%s%02d-%02d-%06d',
                    strtoupper(substr($fn, 0, 1)),
                    rand(1, 99),
                    rand(18, 25),
                    rand(100000, 999999)
                );

                $violator = Violator::create([
                    'lgu_id'              => $lgu->id,
                    'first_name'          => $fn,
                    'middle_name'         => $mn,
                    'last_name'           => $ln,
                    'date_of_birth'       => sprintf('%d-%02d-%02d', rand(1975, 2003), rand(1, 12), rand(1, 28)),
                    'place_of_birth'      => $lgu->name,
                    'gender'              => rand(0, 1) ? 'Male' : 'Female',
                    'civil_status'        => rand(0, 1) ? 'Married' : 'Single',
                    'permanent_address'   => "{$street}, {$lgu->name}, {$lgu->province}",
                    'temporary_address'   => "{$street}, {$lgu->name}, {$lgu->province}",
                    'contact_number'      => sprintf('09%d%08d', rand(15, 99), rand(10000000, 99999999)),
                    'email'               => strtolower("{$fn}.{$ln}{$m}@example.com"),
                    'license_number'      => $licNum,
                    'license_type'        => $licenseTypes[array_rand($licenseTypes)],
                    'license_restriction' => $restrictions[array_rand($restrictions)],
                    'license_issued_date' => now()->subYears(rand(1, 4))->toDateString(),
                    'license_expiry_date' => now()->addYears(rand(1, 5))->toDateString(),
                    'blood_type'          => $bloodTypes[array_rand($bloodTypes)],
                    'height'              => rand(155, 182),
                    'weight'              => rand(50, 90),
                ]);

                $createdViolators->push($violator);

                // Seed 1-2 Vehicles per Motorist
                $vehTypes = ['MC', 'MV'];
                $makes = ['Honda', 'Yamaha', 'Toyota', 'Suzuki', 'Kawasaki', 'Nissan', 'Mitsubishi', 'Hyundai'];
                $models = ['Click 125', 'Mio Aerox', 'Vios 1.5G', 'Raider R150', 'Barako II', 'Navara', 'Mirage G4', 'Accent'];
                $colors = ['Black', 'White', 'Red', 'Blue', 'Silver', 'Matte Gray', 'Orange'];

                $vehCount = rand(1, 2);
                for ($v = 0; $v < $vehCount; $v++) {
                    $vTypeChoice = $vehTypes[array_rand($vehTypes)];
                    $makeChoice  = $makes[array_rand($makes)];
                    $modelChoice = $models[array_rand($models)];
                    $colorChoice = $colors[array_rand($colors)];

                    $platePrefix = strtoupper(Str::random(3));
                    $plateNum    = sprintf('%s %03d', $platePrefix, rand(100, 9999));

                    $vehicle = Vehicle::create([
                        'lgu_id'         => $lgu->id,
                        'violator_id'    => $violator->id,
                        'plate_number'   => $plateNum,
                        'vehicle_type'   => $vTypeChoice,
                        'make'           => $makeChoice,
                        'model'          => $modelChoice,
                        'color'          => $colorChoice,
                        'year'           => rand(2018, 2025),
                        'or_number'      => sprintf('OR-%08d', rand(10000000, 99999999)),
                        'cr_number'      => sprintf('CR-%08d', rand(10000000, 99999999)),
                        'chassis_number' => sprintf('CHS-%s%06d', strtoupper(Str::random(4)), rand(100000, 999999)),
                    ]);

                    $createdVehicles->push($vehicle);
                }
            }
        }

        // ── 4. Seed Violations per current LGU ──
        $violationStatuses = ['pending', 'pending', 'settled', 'settled', 'partial', 'overdue'];

        $lguLandmarks = [
            'BAL' => ['Balamban Public Market', 'Transcentral Highway Corner', 'Nivel Hills Junction', 'Aliwanay Highway', 'Poblacion Plaza'],
            'CEB' => ['Fuente Osmeña Circle', 'SRP Coastal Expressway', 'Colon Street Junction', 'Gorordo Avenue', 'Banilad Flyover'],
            'MAN' => ['Subangdaku Flyover', 'UN Avenue Interchange', 'M.C. Briones Street', 'Hernan Cortes Junction', 'A.C. Cortes Avenue'],
            'DAN' => ['Danao Port Highway', 'Poblacion Commercial Zone', 'Sabang Coastal Road', 'Tuburan-Danao Highway', 'Danao City Plaza'],
            'CAR' => ['Carcar Rotunda Junction', 'Poblacion Heritage Zone', 'Tuyom Coastal Highway', 'Carcar City Market', 'San Fernando Border'],
            'TAL' => ['Tabunok Flyover CSCR', 'Talisay City Hall Intersection', 'Pooc Expressway Link', 'Dumlog Highway', 'Lawaan Intersection'],
            'TOL' => ['Toledo Port Road', 'Sangi Junction', 'Lutopan Highway', 'Dapitan Street Toledo', 'Poblacion Toledo Plaza'],
            'AST' => ['Asturias Central Highway', 'Poblacion Asturias Square', 'Langub Junction', 'Buguion Road', 'Asturias Market'],
            'BAR' => ['Barili Rotunda Highway', 'Mantayupan Road', 'Poblacion Barili Square', 'Japitan Beach Access Road', 'Barili Market Junction'],
        ];

        $lguGps = [
            'BAL' => ['lat' => 10.5015, 'lng' => 123.7170],
            'CEB' => ['lat' => 10.3117, 'lng' => 123.8915],
            'MAN' => ['lat' => 10.3340, 'lng' => 123.9357],
            'DAN' => ['lat' => 10.5255, 'lng' => 124.0270],
            'CAR' => ['lat' => 10.1062, 'lng' => 123.6388],
            'TAL' => ['lat' => 10.2725, 'lng' => 123.8425],
            'TOL' => ['lat' => 10.3782, 'lng' => 123.6372],
            'AST' => ['lat' => 10.5694, 'lng' => 123.7228],
            'BAR' => ['lat' => 10.1167, 'lng' => 123.5167],
        ];

        foreach ($lgus as $lgu) {
            $lguViolators = $createdViolators->where('lgu_id', $lgu->id)->values();
            if ($lguViolators->isEmpty()) {
                $lguViolators = $createdViolators;
            }

            $landmarks = $lguLandmarks[strtoupper($lgu->code)] ?? ["Poblacion Main Road, {$lgu->name}", "National Highway, {$lgu->name}"];
            $baseGps   = $lguGps[strtoupper($lgu->code)] ?? ['lat' => 10.3157, 'lng' => 123.8854];

            $violationCount = rand(5, 7);

            for ($k = 0; $k < $violationCount; $k++) {
                $violator = $lguViolators->random();
                $vehicle  = $createdVehicles->where('violator_id', $violator->id)->first() ?? $createdVehicles->random();

                $type = $vTypes->random();
                $status = $violationStatuses[array_rand($violationStatuses)];

                $daysAgo = rand(1, 90);
                $violationDate = now()->subDays($daysAgo);
                $dueDate = (clone $violationDate)->addDays(3);

                if ($status === 'overdue') {
                    $violationDate = now()->subDays(rand(5, 45));
                    $dueDate = (clone $violationDate)->addDays(3);
                }

                $ticketNum = sprintf('TVIRS-CEB-%s-%d-%06d', strtoupper($lgu->code), now()->year, $ticketCounter++);
                $loc = $landmarks[array_rand($landmarks)];

                $settledAt = null;
                $orNumber  = null;
                $cashierName = null;
                $payMethod = null;

                if ($status === 'settled') {
                    $settledAt = (clone $violationDate)->addDays(rand(0, 2));
                    $orNumber  = sprintf('OR-2026-%06d', rand(100000, 999999));
                    $cashierName = 'Collection Cashier (' . $lgu->name . ')';
                    $payMethod = 'cash';
                }

                $violation = Violation::create([
                    'lgu_id'             => $lgu->id,
                    'violator_id'        => $violator->id,
                    'vehicle_id'         => $vehicle->id,
                    'vehicle_owner_name' => $violator->full_name,
                    'vehicle_plate'      => $vehicle->plate_number,
                    'vehicle_make'       => $vehicle->make,
                    'vehicle_model'      => $vehicle->model,
                    'vehicle_color'      => $vehicle->color,
                    'vehicle_or_number'  => $vehicle->or_number,
                    'vehicle_cr_number'  => $vehicle->cr_number,
                    'vehicle_chassis'    => $vehicle->chassis_number,
                    'violation_type_id'  => $type->id,
                    'date_of_violation'  => $violationDate->toDateString(),
                    'due_date'           => $dueDate->toDateString(),
                    'location'           => $loc,
                    'gps_lat'            => $baseGps['lat'] + (rand(-10, 10) * 0.001),
                    'gps_lng'            => $baseGps['lng'] + (rand(-10, 10) * 0.001),
                    'ticket_number'      => $ticketNum,
                    'status'             => $status === 'overdue' ? 'pending' : $status,
                    'notes'              => 'Ceded citation ticket during routine traffic checkpoint at ' . $loc,
                    'recorded_by'        => $recorder->id,
                    'or_number'          => $orNumber,
                    'cashier_name'       => $cashierName,
                    'payment_method'     => $payMethod,
                    'settled_at'         => $settledAt,
                ]);

                // Create payment entry if settled or partial
                if ($status === 'settled') {
                    Payment::create([
                        'violation_id'   => $violation->id,
                        'collected_by'   => $cashier->id,
                        'or_number'      => $orNumber,
                        'cashier_name'   => $cashierName,
                        'amount_paid'    => $violation->totalAmountDue(),
                        'payment_method' => 'cash',
                        'paid_at'        => $settledAt,
                    ]);
                } elseif ($status === 'partial') {
                    Payment::create([
                        'violation_id'   => $violation->id,
                        'collected_by'   => $cashier->id,
                        'or_number'      => sprintf('OR-PAR-%06d', rand(100000, 999999)),
                        'cashier_name'   => 'Collection Cashier (' . $lgu->name . ')',
                        'amount_paid'    => round($violation->totalAmountDue() / 2, 2),
                        'payment_method' => 'cash',
                        'paid_at'        => (clone $violationDate)->addDay(),
                    ]);
                }
            }
        }

        // ── 5. Seed Incidents per current LGU (Focus on Balamban LGU) ──
        $incidentStatuses = ['reported', 'under_assessment', 'assigned_for_investigation', 'resolved', 'closed'];

        $balambanDescriptions = [
            'Head-on collision between a motorcycle and a light truck near Balamban Public Market due to slippery road conditions.',
            'Side-swipe incident involving a PUJ and private vehicle along Transcentral Highway, Gaas, Balamban.',
            'Stalled cargo truck causing major traffic obstruction and minor rear-end collision along Aliwanay Highway, Balamban.',
            'Self-accident involving a motorcycle skidding off the curve at Nivel Hills, Transcentral Highway, Balamban.',
            'Intersection collision at Poblacion Plaza, Balamban due to failure to yield right-of-way.',
            'Three-vehicle pileup involving two motorcycles and an SUV near Prenza Bridge, Balamban.',
            'Pedestrian knock-down near Bujan Street corner Highway, Balamban.',
            'Vehicle rollover along Pondol Coastal Road, Balamban following brake failure.',
            'T-bone collision at Arpili Road Junction, Balamban during heavy rain.',
            'Collision between a delivery van and a tricycle along Cambuhawe Spring Access Road, Balamban.',
            'Motorcycle collision with a parked vehicle near Cantuod Highway Intersection, Balamban.',
            'Loss of control on steep slope along Transcentral Highway, Cansomoroy, Balamban resulting in property damage.',
            'Minor side-impact collision at Nivel Hills Viewpoint Bend, Balamban.',
            'Truck brake failure near Balamban Industrial Estate Road.',
            'Motorcycle sideswipe near Prenza River Bridge, Balamban.',
        ];

        foreach ($lgus as $lgu) {
            $lguViolators = $createdViolators->where('lgu_id', $lgu->id)->values();
            if ($lguViolators->isEmpty()) {
                $lguViolators = $createdViolators;
            }

            $isBalamban = strtoupper($lgu->code) === 'BAL';
            $landmarks  = $lguLandmarks[strtoupper($lgu->code)] ?? ["Highway Intersection, {$lgu->name}"];
            $baseGps    = $lguGps[strtoupper($lgu->code)] ?? ['lat' => 10.3157, 'lng' => 123.8854];

            $incCount   = $isBalamban ? rand(12, 15) : rand(3, 4);

            for ($i = 0; $i < $incCount; $i++) {
                $incStatus = $incidentStatuses[array_rand($incidentStatuses)];
                $incDate   = now()->subDays(rand(1, 90));
                $loc       = $landmarks[array_rand($landmarks)];

                $incNum = sprintf('INC-%d-%s-%04d', now()->year, strtoupper($lgu->code), $incidentCounter++);

                $desc = $isBalamban
                    ? $balambanDescriptions[$i % count($balambanDescriptions)]
                    : 'Traffic incident reported at ' . $loc . ' involving multi-vehicle impact and minor property damage.';

                $incident = Incident::create([
                    'lgu_id'           => $lgu->id,
                    'recorded_by'      => $recorder->id,
                    'incident_number'  => $incNum,
                    'date_of_incident' => $incDate->toDateTimeString(),
                    'location'         => $loc,
                    'gps_lat'          => $baseGps['lat'] + (rand(-15, 15) * 0.001),
                    'gps_lng'          => $baseGps['lng'] + (rand(-15, 15) * 0.001),
                    'status'           => $incStatus,
                    'description'      => $desc,
                ]);

                // Link 1-2 motorists to the incident
                $partyCount = rand(1, 2);
                for ($p = 0; $p < $partyCount; $p++) {
                    $partyViolator = $lguViolators->random();
                    $partyVehicle  = $createdVehicles->where('violator_id', $partyViolator->id)->first() ?? $createdVehicles->random();
                    $charge        = $cTypes->isNotEmpty() ? $cTypes->random() : null;

                    IncidentMotorist::create([
                        'incident_id'              => $incident->id,
                        'violator_id'              => $partyViolator->id,
                        'vehicle_id'               => $partyVehicle?->id,
                        'incident_charge_type_id' => $charge?->id,
                        'motorist_name'            => $partyViolator->full_name,
                        'motorist_license'         => $partyViolator->license_number,
                        'motorist_contact'         => $partyViolator->contact_number,
                        'motorist_address'         => $partyViolator->permanent_address,
                        'vehicle_plate'            => $partyVehicle?->plate_number,
                        'vehicle_make'             => $partyVehicle?->make,
                        'vehicle_model'            => $partyVehicle?->model,
                        'vehicle_color'            => $partyVehicle?->color,
                        'notes'                    => 'Party listed in spot investigation report.',
                    ]);
                }
            }
        }
    }
}
