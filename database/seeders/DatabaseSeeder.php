<?php

namespace Database\Seeders;

use App\Models\IncidentChargeType;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bootstrap operator account — only create if it doesn't exist yet,
        // so subsequent deploys never overwrite a changed password.
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD');
        if (! $adminPassword) {
            throw new \RuntimeException('DEFAULT_ADMIN_PASSWORD must be set in your .env before seeding.');
        }

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrator',
                'role'     => 'operator',
                'password' => Hash::make($adminPassword),
            ]
        );

        // Seed common traffic violation types
        $violationTypes = [
            // ── Licensing & Registration ──
            ['name' => 'No Driver\'s License',          'description' => 'Operating a vehicle without a valid driver\'s license.',              'fine_amount' => 3000.00],
            ['name' => 'Expired Driver\'s License',     'description' => 'Operating a vehicle with an expired driver\'s license.',              'fine_amount' => 3000.00],
            ['name' => 'No Vehicle Registration',       'description' => 'Operating a vehicle without valid LTO registration.',                 'fine_amount' => 3000.00],
            ['name' => 'No OR/CR',                      'description' => 'Failure to carry or present Official Receipt and Certificate of Registration.', 'fine_amount' => 1000.00],
            ['name' => 'No Number Plate',               'description' => 'Operating a vehicle with missing, unreadable, or unauthorized plate.','fine_amount' => 2000.00],
            ['name' => 'Underage Driver',               'description' => 'Allowing or operating a vehicle while below the legal driving age.',  'fine_amount' => 3000.00],

            // ── Safety & Equipment ──
            ['name' => 'No Helmet',                     'description' => 'Motorcycle rider or passenger not wearing a standard helmet.',        'fine_amount' => 1500.00],
            ['name' => 'No Seatbelt',                   'description' => 'Driver or front-seat passenger not wearing a seatbelt.',              'fine_amount' => 1000.00],
            ['name' => 'No Child Restraint',            'description' => 'Failure to use a child car seat for children below the required age.','fine_amount' => 1000.00],
            ['name' => 'Defective Equipment',           'description' => 'Operating a vehicle with defective lights, brakes, or other safety equipment.', 'fine_amount' => 2000.00],
            ['name' => 'Smoke Belching',                'description' => 'Emitting excessive smoke beyond the allowable emission standard.',    'fine_amount' => 2000.00],
            ['name' => 'Illegal Modification',          'description' => 'Vehicle modified in a manner not approved by the LTO.',               'fine_amount' => 5000.00],

            // ── Traffic Rules & Road Behavior ──
            ['name' => 'Reckless Driving',              'description' => 'Operating a vehicle in a reckless, careless, or negligent manner.',   'fine_amount' => 2000.00],
            ['name' => 'Speeding',                      'description' => 'Exceeding the posted speed limit.',                                   'fine_amount' => 2000.00],
            ['name' => 'Beating Red Light',             'description' => 'Passing through a red traffic signal.',                              'fine_amount' => 1500.00],
            ['name' => 'Beating Yellow Light',          'description' => 'Failing to stop when a traffic signal turns yellow.',                 'fine_amount' => 1000.00],
            ['name' => 'Counterflow',                   'description' => 'Driving against the designated flow of traffic.',                     'fine_amount' => 1500.00],
            ['name' => 'Illegal U-Turn',                'description' => 'Making a U-turn in a prohibited area or without proper signal.',      'fine_amount' => 1000.00],
            ['name' => 'Illegal Overtaking',            'description' => 'Overtaking in a no-overtaking zone or in an unsafe manner.',          'fine_amount' => 1500.00],
            ['name' => 'Wrong Lane',                    'description' => 'Driving in a lane not designated for the vehicle type or direction.',  'fine_amount' => 1000.00],
            ['name' => 'Failure to Yield',              'description' => 'Failure to give way to pedestrians, emergency vehicles, or right-of-way vehicles.', 'fine_amount' => 1000.00],
            ['name' => 'Obstruction',                   'description' => 'Blocking traffic flow or road access.',                               'fine_amount' => 1000.00],
            ['name' => 'Illegal Parking',               'description' => 'Parking in a prohibited or no-parking zone.',                        'fine_amount' => 1000.00],
            ['name' => 'Loading / Unloading Violation', 'description' => 'Loading or unloading passengers in a prohibited zone.',               'fine_amount' => 1000.00],
            ['name' => 'Stalled / Abandoned Vehicle',   'description' => 'Leaving a stalled or abandoned vehicle on the roadway without markings.', 'fine_amount' => 1000.00],
            ['name' => 'Drag Racing / Stunt Driving',   'description' => 'Racing another vehicle or performing stunts on a public road.',       'fine_amount' => 5000.00],

            // ── Impaired Driving ──
            ['name' => 'Drunk Driving (DUI)',           'description' => 'Operating a vehicle under the influence of alcohol (RA 10586).',      'fine_amount' => 5000.00],
            ['name' => 'Driving Under the Influence of Drugs', 'description' => 'Operating a vehicle while impaired by illegal substances.',    'fine_amount' => 5000.00],
            ['name' => 'Distracted Driving',            'description' => 'Using a mobile phone or other electronic device while driving (Anti-Distracted Driving Act).', 'fine_amount' => 5000.00],

            // ── Passengers & Load ──
            ['name' => 'Overloading (Passengers)',      'description' => 'Carrying more passengers than the vehicle\'s seating capacity.',      'fine_amount' => 3000.00],
            ['name' => 'Overloading (Cargo)',           'description' => 'Exceeding the maximum cargo load capacity of the vehicle.',            'fine_amount' => 5000.00],
            ['name' => 'Colorum / Unauthorized Operation', 'description' => 'Operating a public utility vehicle without a valid franchise.',    'fine_amount' => 5000.00],
        ];

        foreach ($violationTypes as $type) {
            ViolationType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        // Seed incident charge types (Art. 365 RPC – Reckless Imprudence & Related)
        $chargeTypes = [
            ['name' => 'Reckless Imprudence Resulting in Homicide',
             'description' => 'Art. 365, RPC — Death caused by reckless or negligent driving.'],
            ['name' => 'Reckless Imprudence Resulting in Double Homicide',
             'description' => 'Art. 365, RPC — Death of two or more persons due to reckless driving.'],
            ['name' => 'Reckless Imprudence Resulting in Serious Physical Injuries',
             'description' => 'Art. 365, RPC — Serious injuries caused by reckless or negligent driving.'],
            ['name' => 'Reckless Imprudence Resulting in Less Serious Physical Injuries',
             'description' => 'Art. 365, RPC — Less serious injuries caused by reckless or negligent driving.'],
            ['name' => 'Reckless Imprudence Resulting in Slight Physical Injuries',
             'description' => 'Art. 365, RPC — Slight injuries caused by reckless or negligent driving.'],
            ['name' => 'Reckless Imprudence Resulting in Damage to Property',
             'description' => 'Art. 365, RPC — Property damage caused by reckless or negligent driving.'],
            ['name' => 'Reckless Imprudence Resulting in Homicide and Damage to Property',
             'description' => 'Art. 365, RPC — Death and property damage caused by reckless driving.'],
            ['name' => 'Reckless Imprudence Resulting in Physical Injuries and Damage to Property',
             'description' => 'Art. 365, RPC — Physical injuries and property damage from reckless driving.'],
            ['name' => 'Simple Imprudence Resulting in Physical Injuries',
             'description' => 'Art. 365, RPC — Injuries resulting from simple lack of precaution.'],
            ['name' => 'Simple Imprudence Resulting in Damage to Property',
             'description' => 'Art. 365, RPC — Property damage resulting from simple lack of precaution.'],
            ['name' => 'Violation of Land Transportation and Traffic Code (RA 4136)',
             'description' => 'Breach of provisions of the Land Transportation and Traffic Code.'],
            ['name' => 'Driving Under the Influence (RA 10586)',
             'description' => 'Anti-Drunk and Drugged Driving Act — operating a vehicle while impaired.'],
            ['name' => 'Hit and Run',
             'description' => 'Leaving the scene of an accident without rendering aid or reporting to authorities.'],
            ['name' => 'Obstruction of Justice / Failure to Report',
             'description' => 'Failure to report a traffic incident to law enforcement as required.'],
        ];

        foreach ($chargeTypes as $charge) {
            IncidentChargeType::firstOrCreate(
                ['name' => $charge['name']],
                $charge
            );
        }

        // DemoDataSeeder intentionally removed — production data only
    }
}
