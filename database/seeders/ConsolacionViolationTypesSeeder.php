<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Lgu;
use App\Models\ViolationType;

class ConsolacionViolationTypesSeeder extends Seeder
{
    public function run(): void
    {
        $consolacion = Lgu::firstOrCreate(
            ['code' => 'CON'],
            [
                'name'                   => 'Consolacion',
                'province'               => 'Cebu',
                'psgc_city_code'         => '072219000',
                'police_station_name'    => 'CONSOLACION MUNICIPAL POLICE STATION',
                'police_station_address' => 'Poblacion, Consolacion, Cebu',
                'ordinance_reference'    => 'Ord. No. 34, S. 2025 as amended by Ord. No. 13, S. 2026 (Consolacion Revised Traffic Code)',
            ]
        );

        $consolacionLguId = $consolacion->id;

        DB::transaction(function () use ($consolacion, $consolacionLguId) {
            $consolacion->update([
                'ordinance_reference' => 'Ord. No. 34, S. 2025 as amended by Ord. No. 13, S. 2026 (Consolacion Revised Traffic Code)',
            ]);

            $violations = [
                // ── Parking & Road Obstruction ──
                [
                    'code' => 'CON-01',
                    'name' => 'No Parking',
                    'description' => 'Parking in areas designated with No Parking signs or zones (Sec. 9.ii / Sec. 22.2).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-02',
                    'name' => 'Illegal Parking on Public Roads',
                    'description' => 'Illegal parking along National Highway, Provincial, Municipal, and Barangay roads (Ord. 13, S. 2026 / Sec. 22.6).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-03',
                    'name' => 'Double Parking',
                    'description' => 'Parking a vehicle alongside another vehicle already parked parallel to the curb or road edge (Sec. 9.iii).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-04',
                    'name' => 'Overnight Parking at Terminal and Public Market',
                    'description' => 'Unauthorized overnight parking at the terminal and public market grounds (Ord. 13, S. 2026 / Sec. 22.7).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-05',
                    'name' => 'No Stopping',
                    'description' => 'Stopping or halting a vehicle in designated No Stopping zones (Sec. 9.iv / Sec. 22.3).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-06',
                    'name' => 'No Loading and Unloading',
                    'description' => 'Loading or unloading passengers or cargoes in unauthorized or No Loading/Unloading zones (Sec. 9.v / Sec. 22.4).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-07',
                    'name' => 'Obstruction to Traffic Sidewalk / Public Roadway',
                    'description' => 'Causing obstruction to pedestrian sidewalks or vehicular traffic flow (Sec. 9.xx, xxi / Sec. 22.21).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-08',
                    'name' => 'Abandoned / Parked Container Van on Public Roads',
                    'description' => 'Abandoned or parked container van on Provincial, Municipal, or Barangay roads (Ord. 13, S. 2026 / Sec. 22.8: ₱2,500 1st day + ₱100/succeeding day).',
                    'fine_amount' => 2500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-09',
                    'name' => 'Trailers of Container Vans Without Safety Lock Devices',
                    'description' => 'Operating trailers of container vans without using required safety lock devices (Ord. 13, S. 2026 / Sec. 22.4).',
                    'fine_amount' => 1500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-10',
                    'name' => 'Wheel Clamping Violation',
                    'description' => 'Unauthorized stopping, standing, or parking in designated clamping zones (Ord. 13, S. 2026 / Sec. 22.11: ₱2,000 1st 24 hrs + ₱500/succeeding 24 hrs).',
                    'fine_amount' => 2000.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-11',
                    'name' => 'Destroying, Tampering, or Disfiguring Wheel Clamp',
                    'description' => 'Unlawfully removing, tampering with, destroying, or disfiguring an authorized wheel clamp (Ord. 13, S. 2026 / Sec. 22.12: ₱2,500 + clamp cost).',
                    'fine_amount' => 2500.00,
                    'points' => 3,
                ],

                // ── Traffic Movement & Turning ──
                [
                    'code' => 'CON-12',
                    'name' => 'No Entry',
                    'description' => 'Entering or driving into a roadway with a No Entry sign (Sec. 9.i / Sec. 22.1).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-13',
                    'name' => 'No U-Turn',
                    'description' => 'Executing a U-turn at an intersection or area where U-turns are prohibited (Sec. 9.vi / Sec. 22.5).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-14',
                    'name' => 'No Left Turn',
                    'description' => 'Making an unauthorized left turn at intersections with No Left Turn signs (Sec. 9.vii / Sec. 22.6).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-15',
                    'name' => 'No Right Turn',
                    'description' => 'Making an unauthorized right turn at intersections with No Right Turn signs (Sec. 9.vii / Sec. 22.7).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-16',
                    'name' => 'Beating the Red Light',
                    'description' => 'Disregarding traffic signals / proceeding through an intersection on a red signal (Sec. 10.A / Sec. 22.8 / Ord. 13 Sec. 22.10).',
                    'fine_amount' => 1000.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-17',
                    'name' => 'Swerving / Changing of Lane / Counter Flow',
                    'description' => 'Dangerous lane changing, reckless swerving, or counterflow driving against traffic direction (Sec. 9.xv / Sec. 22.16).',
                    'fine_amount' => 1000.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-18',
                    'name' => 'Truck Ban Violation',
                    'description' => 'Traversing National Highway or North Coastal Road with heavy trucks >4,500 kg GVW during ban hours (6:00-8:00 AM & 5:00-7:00 PM) (Ord. 13, S. 2026 / Sec. 16 / Sec. 22.9).',
                    'fine_amount' => 2500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-19',
                    'name' => 'Speeding / Speed Limit Violation',
                    'description' => 'Operating a vehicle in excess of established speed limits on Provincial, Municipal, Barangay, or Crowded streets (Sec. 8.A).',
                    'fine_amount' => 1000.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-20',
                    'name' => 'Engaging in Speed Contest / Drag Racing',
                    'description' => 'Engaging in or aiding any motor vehicle speed contest or exhibition of speed on public streets (Sec. 8.B).',
                    'fine_amount' => 2000.00,
                    'points' => 3,
                ],

                // ── Licensing & Driver Conduct ──
                [
                    'code' => 'CON-21',
                    'name' => 'Driving Without License',
                    'description' => 'Driving a motor vehicle without a valid driver\'s license (Sec. 9.viii.b / Sec. 22.9.2). Subject to vehicle impoundment.',
                    'fine_amount' => 2500.00,
                    'points' => 3,
                ],
                [
                    'code' => 'CON-22',
                    'name' => 'Driving Under the Influence (DUI) of Liquor or Drugs',
                    'description' => 'Operating a motor vehicle while under the influence of alcohol or prohibited drugs (Sec. 9.viii.a / Sec. 22.9.1).',
                    'fine_amount' => 1000.00,
                    'points' => 3,
                ],
                [
                    'code' => 'CON-23',
                    'name' => 'Driving with Student Permit Only (Unsupervised)',
                    'description' => 'Driving with a student permit only without the physical supervision of a licensed professional driver (Sec. 9.viii.c / Sec. 22.9.3).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-24',
                    'name' => 'Driving with Other Person\'s Driver\'s License',
                    'description' => 'Operating a motor vehicle using a driver\'s license belonging to another person (Sec. 9.viii.d / Sec. 22.9.4).',
                    'fine_amount' => 1500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-25',
                    'name' => 'Driving with Spurious / Fake Driver\'s License',
                    'description' => 'Operating a motor vehicle using a spurious, forged, or fake driver\'s license (Sec. 9.viii.e / Sec. 22.9.5).',
                    'fine_amount' => 1500.00,
                    'points' => 3,
                ],
                [
                    'code' => 'CON-26',
                    'name' => 'Using Mobile Phone While Driving / Distracted Driving',
                    'description' => 'Using mobile phone or electronic device while vehicle is in motion or stopped at intersection (RA 10913 / Sec. 10.B / Ord. 13 Sec. 22.3).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-27',
                    'name' => 'Driving Half-Naked',
                    'description' => 'Operating a motor vehicle or motorcycle without upper garment / half-naked on public roads (Sec. 22.25).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-28',
                    'name' => 'Intentionally Concealing Identity by Covering Face',
                    'description' => 'Intentionally concealing one\'s identity by covering the face while operating a vehicle (Sec. 9.xxii / Sec. 22.22).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-29',
                    'name' => 'Disobedience to Police and Traffic Officers',
                    'description' => 'Willfully failing or refusing to comply with lawful order or direction of police or traffic enforcer (Sec. 6.C).',
                    'fine_amount' => 1000.00,
                    'points' => 2,
                ],

                // ── Plates & Registration ──
                [
                    'code' => 'CON-30',
                    'name' => 'No Registration / Unregistered Vehicle (No OR/CR)',
                    'description' => 'Driving unregistered vehicle or failing to present OR/CR upon apprehension (Sec. 9.xvi / Sec. 22.17). Subject to impoundment.',
                    'fine_amount' => 2500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-31',
                    'name' => 'No Plate Number Attached',
                    'description' => 'Failure to attach or affix duly issued license plate on the vehicle (Sec. 9.ix / Sec. 22.10). Subject to impoundment.',
                    'fine_amount' => 1500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-32',
                    'name' => 'Improper Display of Plate Number',
                    'description' => 'Displaying plate number improperly, obscured, tilted, or covered (Sec. 9.x / Sec. 22.11).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-33',
                    'name' => 'Using Improvised Plate Numbers',
                    'description' => 'Using unauthorized or homemade improvised plates without official LTO authority (Sec. 9.xvii / Sec. 22.18).',
                    'fine_amount' => 1500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-34',
                    'name' => 'Unauthorized Use of Temporary Plate Numbers',
                    'description' => 'Unauthorized or expired use of temporary plate numbers (Sec. 9.xviii / Sec. 22.19).',
                    'fine_amount' => 1500.00,
                    'points' => 2,
                ],

                // ── Equipment, Lights & Noise ──
                [
                    'code' => 'CON-35',
                    'name' => 'No Helmet / Non-Wearing of Helmet',
                    'description' => 'Motorcycle rider or backrider not wearing standard protective motorcycle helmet (Sec. 9.xxiii, xxiv / Sec. 22.23, 22.24).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-36',
                    'name' => 'Defective / Non-Installation of Headlight',
                    'description' => 'Operating a vehicle with defective, burnt-out, or missing headlight (Sec. 9.xi.a / Sec. 22.12.1).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-37',
                    'name' => 'Defective / Non-Installation of Signal Light',
                    'description' => 'Operating a vehicle with defective or non-working turn signal indicator lights (Sec. 9.xi.b / Sec. 22.12.2).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-38',
                    'name' => 'Defective / Non-Installation of Tail Light',
                    'description' => 'Operating a vehicle with defective, broken, or missing tail light (Sec. 9.xi.c / Sec. 22.12.3).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-39',
                    'name' => 'Defective / Non-Installation of Brake Light',
                    'description' => 'Operating a vehicle with defective, broken, or missing brake lights (Sec. 9.xi.d / Sec. 22.12.4).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-40',
                    'name' => 'No Side Mirror',
                    'description' => 'Operating vehicle or motorcycle without required installed side rearview mirrors (Sec. 22.12.5: ₱1,000 each).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-41',
                    'name' => 'Loud Improvised Muffler',
                    'description' => 'Using modified loud pipe / improvised muffler exceeding allowed decibel levels (Sec. 9.xii / Sec. 22.13: ₱1,000 + removal/confiscation).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-42',
                    'name' => 'Excessive Volume / Sound of Car Stereos or Radios',
                    'description' => 'Operating sound systems, car stereos, or radios at excessive volume (Sec. 9.xiii / Sec. 22.14).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-43',
                    'name' => 'Inappropriate Sound of Horns',
                    'description' => 'Excessive or inappropriate sounding of horn in restricted areas or without valid cause (Sec. 9.xix / Sec. 22.20).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-44',
                    'name' => 'Illegal Use of Reflectorized Gadgets and Halogen Lamps',
                    'description' => 'Unauthorized use or installation of illegal reflectorized gadgets, halogen lamps, or blinding auxiliary lights (Sec. 9.xxv / Sec. 22.28).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],

                // ── Load, Environment, Accidents & Pedestrians ──
                [
                    'code' => 'CON-45',
                    'name' => 'Overloading of Passengers or Cargoes',
                    'description' => 'Exceeding vehicle rated capacity for passengers or cargo weight limits (Sec. 9.viii.f / Sec. 22.9.6).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-46',
                    'name' => 'Smoke Belching / Clean Air Act Violation',
                    'description' => 'Emitting black smoke in violation of the Philippine Clean Air Act standards (Sec. 9.xiv / Sec. 22.15).',
                    'fine_amount' => 1000.00,
                    'points' => 1,
                ],
                [
                    'code' => 'CON-47',
                    'name' => 'Impounding of Trucks for MENRO / PENRO Violations',
                    'description' => 'Dump trucks and cargo trucks apprehended for environmental violations (Ord. 13, S. 2026 / Sec. 22.5: ₱2,500 1st day + ₱50/succeeding day).',
                    'fine_amount' => 2500.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-48',
                    'name' => 'Accident Resulting in Damage to Public Property',
                    'description' => 'Vehicular accident causing damage to public / government property (Ord. 13, S. 2026 / Sec. 22.2 / Sec. 9.xxvii: to be determined by Municipal Engineering Office).',
                    'fine_amount' => 1000.00,
                    'points' => 2,
                ],
                [
                    'code' => 'CON-49',
                    'name' => 'Anti-Jaywalking',
                    'description' => 'Crossing streets outside designated pedestrian lanes or ignoring pedestrian signals (Sec. 17 / Ord. 13 Sec. 22.13).',
                    'fine_amount' => 50.00,
                    'points' => 0,
                ],
                [
                    'code' => 'CON-50',
                    'name' => 'Restricted Use of Coasters / Skateboards / Toy Vehicles on Roadway',
                    'description' => 'Riding coasters, roller skates, skateboards, or toy vehicles on roadway except when directly crossing (Sec. 6.E).',
                    'fine_amount' => 300.00,
                    'points' => 0,
                ],
            ];

            foreach ($violations as $data) {
                ViolationType::updateOrCreate(
                    [
                        'lgu_id' => $consolacionLguId,
                        'name'   => $data['name'],
                    ],
                    [
                        'code'                => $data['code'],
                        'description'         => $data['description'],
                        'fine_amount'         => $data['fine_amount'],
                        'points'              => $data['points'],
                        'late_penalty_amount' => null,
                    ]
                );
            }

            Cache::forget('violation_types');
            Cache::forget("violation_types_lgu_{$consolacionLguId}");
        });
    }
}
