<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Lgu;
use App\Models\ViolationType;
use App\Models\Violation;

return new class extends Migration
{
    public function up(): void
    {
        $asturias = Lgu::where('name', 'like', '%Asturias%')->first();
        if (!$asturias) {
            return;
        }

        $asturiasLguId = $asturias->id;

        DB::transaction(function () use ($asturiasLguId) {
            // 1. Re-link any misaligned violations from other LGUs that pointed to old Asturias IDs
            $cebuChildRestraint = ViolationType::where('lgu_id', 3)->where('name', 'No Child Restraint')->value('id');
            if ($cebuChildRestraint) {
                Violation::where('lgu_id', 3)->whereIn('violation_type_id', function ($query) use ($asturiasLguId) {
                    $query->select('id')->from('violation_types')->where('lgu_id', $asturiasLguId);
                })->update(['violation_type_id' => $cebuChildRestraint]);
            }

            $carcarYellowLight = ViolationType::where('lgu_id', 6)->where('name', 'Beating Yellow Light')->value('id');
            if ($carcarYellowLight) {
                Violation::where('lgu_id', 6)->whereIn('violation_type_id', function ($query) use ($asturiasLguId) {
                    $query->select('id')->from('violation_types')->where('lgu_id', $asturiasLguId);
                })->update(['violation_type_id' => $carcarYellowLight]);
            }

            // 2. Define the 34 new Asturias ordinances from the 2016 Ordinance Excel
            $newOrdinances = [
                [
                    'name' => "Failure to Carry a Driver's License",
                    'description' => "Failure to have a valid license to drive a motor vehicle in one's possession.",
                    'fine_amount' => 200.00,
                ],
                [
                    'name' => "Driving with a Delinquent or Invalid Driver's License",
                    'description' => "Driving a vehicle with a delinquent or invalid driver's license.",
                    'fine_amount' => 250.00,
                ],
                [
                    'name' => "Driving a Motor Vehicle Without a Driver's License",
                    'description' => "Driving a motor vehicle without first securing a driver's license.",
                    'fine_amount' => 500.00,
                ],
                [
                    'name' => "Lights",
                    'description' => "Violation of the lighting requirements under Section 8. (Penalty: Not less than ₱100.00 per violation)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Right-Side Driving",
                    'description' => "Failure to observe the right-side driving rule under Section 9. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Speed Restrictions",
                    'description' => "Violation of the speed restrictions under Section 10. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Overtaking a Vehicle",
                    'description' => "Violation of the rules on overtaking under Section 11. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Giving Way of Overtaking Vehicle",
                    'description' => "Failure to comply with the rule on giving way when being overtaken under Section 12. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Restriction on Overtaking and Passing",
                    'description' => "Violation of the restrictions on overtaking and passing under Section 13. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Right of Way",
                    'description' => "Failure to observe the right-of-way rules under Section 14. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Exemption to Right of Way Rule",
                    'description' => "Violation of the provisions concerning exemptions to the right-of-way rule under Section 15. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Signal on Starting, Stopping or Turning",
                    'description' => "Failure to give the required signal when starting, stopping, or turning under Section 16. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Turning at Intersections",
                    'description' => "Failure to observe the prescribed rules when turning at intersections under Section 17. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Stopping, Standing or Parking Prohibited in Specified Places",
                    'description' => "Stopping or parking in places prohibited under Section 18. (Penalty: Not less than ₱100.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Traffic Control Devices",
                    'description' => "Failure to obey official traffic control devices under Section 20. (1st offense: ₱1,000.00; 2nd: ₱2,000.00; 3rd: ₱2,500.00 or imprisonment of not less than 3 days, or both)",
                    'fine_amount' => 1000.00,
                ],
                [
                    'name' => "Interference with Official Traffic Controls Devices, Signs",
                    'description' => "Altering, defacing, injuring, knocking down, or removing official traffic control devices or signs without lawful authority. (1st offense: ₱1,000.00; 2nd: ₱2,000.00; 3rd: ₱2,500.00 or imprisonment of not less than 3 days, or both)",
                    'fine_amount' => 1000.00,
                ],
                [
                    'name' => "Reckless Driving",
                    'description' => "Operating a motor vehicle recklessly or without reasonable caution as described in Section 21. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Right of Way for Police and Other Emergency Vehicles",
                    'description' => "Failure to yield and stop as required upon approach of police, fire department, or ambulance vehicles giving an audible signal. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Hitching to a Vehicle",
                    'description' => "Hanging on to, riding on the outside or rear of a vehicle, or hitching to a moving vehicle as prohibited by Section 23. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Driving while under the Influence of Liquor or Narcotic Drugs",
                    'description' => "Driving a motor vehicle while under the influence of liquor or narcotic drugs. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Obstruction of Traffic",
                    'description' => "Driving so as to obstruct or impede the passage of vehicles, including while discharging/taking passengers or loading/unloading freight. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Regulating the Use of Public Roads, Sidewalks, Alleys, or Lanes",
                    'description' => "Using public roads, sidewalks, alleys, or lanes for the prohibited purposes listed in Section 26. (1st violation: warning; 2nd violation: ₱500.00 or imprisonment of 10–30 days, or both)",
                    'fine_amount' => 500.00,
                ],
                [
                    'name' => "Abandoned Motor Vehicles/Trailers",
                    'description' => "Abandoning a vehicle on a public highway; leaving it unattended for more than 24 hours constitutes abandonment under Section 27. (1st offense: ₱200.00; 2nd: ₱300.00; 3rd: ₱500.00 or imprisonment of not less than 2 days, or both; owner also liable for reasonable removal expenses)",
                    'fine_amount' => 200.00,
                ],
                [
                    'name' => "Unattended Motor Vehicle",
                    'description' => "Leaving a motor vehicle unattended without stopping the engine, removing the ignition key, and effectively setting the brakes as required by Section 27. (1st offense: ₱200.00; 2nd: ₱300.00; 3rd: ₱500.00 or imprisonment of not less than 2 days, or both)",
                    'fine_amount' => 200.00,
                ],
                [
                    'name' => "Deny Conveyance of Passengers",
                    'description' => "A passenger vehicle or tricycle shall not deny conveyance of passengers to a proper destination within the municipality except for the stated reasonable causes. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "No Parking on Loading and Unloading Zones",
                    'description' => "Motor vehicles, including motorcycles, are not allowed to park in designated loading and unloading zones. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Overnight Parking Prohibited",
                    'description' => "Overnight parking on national roads for cargo/delivery trucks, service vehicles, tankers, heavy equipment and passenger vehicles from 9:00 P.M. to 6:00 A.M. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Person Propelling Push Cart to Obey Traffic Regulations",
                    'description' => "A person propelling a push cart upon a roadway is subject to the provisions of the Code applicable to the driver of any vehicle, except those inapplicable by their nature. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Use of Coasters, Roller Skates and Similar Devices Restricted",
                    'description' => "Using roller skates, coasters, toy vehicles, or similar devices upon a roadway except while crossing a street. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "No Parking Zone",
                    'description' => "Parking in secondary streets declared as No Parking Zones or other areas designated as no parking under the Code. (Motorcycle/tricycle: ₱100.00; 4-wheel: ₱200.00; 6-wheel and up: ₱400.00)",
                    'fine_amount' => 100.00,
                ],
                [
                    'name' => "Off-Street Parking Facilities and Transport Terminals",
                    'description' => "Violation of the regulated acts and requirements for off-street parking facilities and transport terminals under Section 40. (Penalty: ₱300.00–₱1,000.00 and/or imprisonment of not less than 5 days)",
                    'fine_amount' => 300.00,
                ],
                [
                    'name' => "Bicycle Registration",
                    'description' => "Operating a bicycle without complying with the registration and related requirements under Section 41. (Impoundment; 1st offense ₱10.00; 2nd ₱20.00; 3rd ₱30.00; subsequent offenses + ₱5.00/day after the first 2 days of impoundment)",
                    'fine_amount' => 10.00,
                ],
                [
                    'name' => "Motorized Tricycle-for-Hire Without Franchise",
                    'description' => "Operating a motorized tricycle-for-hire without first securing a franchise, Mayor's Permit and license as required by Section 42. (Penalty: ₱500.00–₱1,000.00 or imprisonment of 10 days–6 months, or both)",
                    'fine_amount' => 500.00,
                ],
                [
                    'name' => "Excessive Motorized Tricycle Fare",
                    'description' => "Charging fares higher than the fare rates fixed under the Code. (1st offense: ₱200.00; 2nd: ₱500.00; 3rd: ₱1,000.00 + revocation of franchise/permit)",
                    'fine_amount' => 200.00,
                ],
            ];

            // 3. Create the new Asturias types first
            $createdTypeMap = [];
            foreach ($newOrdinances as $ord) {
                $created = ViolationType::create([
                    'lgu_id' => $asturiasLguId,
                    'name' => $ord['name'],
                    'description' => $ord['description'],
                    'fine_amount' => $ord['fine_amount'],
                    'late_penalty_amount' => null,
                    'points' => 0,
                ]);
                $createdTypeMap[$ord['name']] = $created->id;
            }

            // 4. Map existing Asturias violations to the closest new Asturias type
            // E.g. Defective Equipment -> Lights
            if (isset($createdTypeMap['Lights'])) {
                Violation::where('lgu_id', $asturiasLguId)
                    ->whereIn('violation_type_id', function ($query) use ($asturiasLguId, $createdTypeMap) {
                        $query->select('id')->from('violation_types')
                            ->where('lgu_id', $asturiasLguId)
                            ->where('name', 'Defective Equipment')
                            ->whereNotIn('id', array_values($createdTypeMap));
                    })->update(['violation_type_id' => $createdTypeMap['Lights']]);
            }

            // Counterflow -> Right-Side Driving
            if (isset($createdTypeMap['Right-Side Driving'])) {
                Violation::where('lgu_id', $asturiasLguId)
                    ->whereIn('violation_type_id', function ($query) use ($asturiasLguId, $createdTypeMap) {
                        $query->select('id')->from('violation_types')
                            ->where('lgu_id', $asturiasLguId)
                            ->where('name', 'Counterflow')
                            ->whereNotIn('id', array_values($createdTypeMap));
                    })->update(['violation_type_id' => $createdTypeMap['Right-Side Driving']]);
            }

            // Fallback for any other Asturias violation pointing to old types
            $defaultNewId = $createdTypeMap["Driving a Motor Vehicle Without a Driver's License"] ?? reset($createdTypeMap);
            Violation::where('lgu_id', $asturiasLguId)
                ->whereNotIn('violation_type_id', array_values($createdTypeMap))
                ->update(['violation_type_id' => $defaultNewId]);

            // 5. Delete all old Asturias violation types that are not in the new list
            ViolationType::where('lgu_id', $asturiasLguId)
                ->whereNotIn('id', array_values($createdTypeMap))
                ->delete();

            // Clear cache
            Cache::forget('violation_types');
            Cache::forget("violation_types_lgu_{$asturiasLguId}");
        });
    }

    public function down(): void
    {
        // No destructive rollback
    }
};
