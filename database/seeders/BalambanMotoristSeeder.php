<?php

namespace Database\Seeders;

use App\Models\Lgu;
use App\Models\Violator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BalambanMotoristSeeder extends Seeder
{
    public function run(): void
    {
        $lguId = Lgu::where('code', 'BAL')->value('id');

        if (!$lguId) {
            $this->command?->error('Balamban (BAL) LGU not found — aborting.');
            return;
        }

        $faker = \Faker\Factory::create();

        $barangays = [
            'Poblacion', 'Abucayan', 'Anapog-Sibugay', 'Aliwanay', 'Adorable',
            'Bayabas', 'Bunga Ilaya', 'Bunga Mar', 'Cambanay', 'Cambangkaya',
            'Canduman', 'Coro', 'Dalahikan', 'Duangan', 'Gaas',
            'Liboron', 'Lugo', 'Manipis', 'Matun-og', 'Pungtod',
        ];

        $maleFirstNames = [
            'Jose', 'Juan', 'Antonio', 'Ricardo', 'Rodrigo', 'Emmanuel', 'Marlon',
            'Renato', 'Danilo', 'Eduardo', 'Ferdinand', 'Rolando', 'Jerome', 'Alvin',
            'Bryan', 'Christian', 'Dennis', 'Edgar', 'Felipe', 'Gilbert', 'Henry',
            'Ismael', 'Joel', 'Kevin', 'Leo', 'Manuel', 'Noel', 'Oliver', 'Paolo',
            'Raymond', 'Samuel', 'Teodoro', 'Vicente', 'Warren', 'Angelo', 'Benedict',
        ];
        $femaleFirstNames = [
            'Maria', 'Angelica', 'Kristine', 'Josephine', 'Maricel', 'Carmela',
            'Divina', 'Elena', 'Fe', 'Grace', 'Helen', 'Imelda', 'Jocelyn', 'Karen',
            'Luzviminda', 'Marites', 'Nenita', 'Ofelia', 'Precious', 'Rosalinda',
            'Susana', 'Teresita', 'Vilma', 'Wilma', 'Ana', 'Bea', 'Cecilia', 'Daisy',
            'Erlinda', 'Flordeliza', 'Gemma', 'Honeylet', 'Irish', 'Jasmine', 'Kimberly',
        ];
        $surnames = [
            'Reyes', 'Cruz', 'Bautista', 'Garcia', 'Torres', 'Mendoza', 'Villanueva',
            'Santos', 'Ramos', 'Aquino', 'Fernandez', 'Gonzales', 'Lim', 'Aguilar',
            'Castillo', 'Navarro', 'Domingo', 'Salazar', 'Rivera', 'Padilla',
            'Marasigan', 'Espiritu', 'Delos Santos', 'Pascual', 'Manalo', 'Ocampo',
            'Bernardo', 'Del Rosario', 'Tan', 'Chua', 'Salvador', 'Ibarra', 'Abella',
            'Dizon', 'Galang', 'Hernandez', 'Jimenez', 'Lacson', 'Macaraeg', 'Nazareno',
            'Olivar', 'Pineda', 'Quiambao', 'Rosales', 'Sarmiento', 'Trinidad', 'Uy',
            'Valdez', 'Yap', 'Zamora',
        ];

        $licenseTypes         = ['Non-Professional', 'Professional'];
        $restrictionPool      = ['1', '2', '3', '4', '1,2', '1,2,3', '1,2,3,4', '2,3'];
        $genders               = ['Male', 'Female'];
        $civilStatuses         = ['Single', 'Married', 'Widowed', 'Separated'];
        $bloodTypes            = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

        $created = 0;

        for ($i = 0; $i < 50; $i++) {
            $gender    = $faker->randomElement($genders);
            $firstName = $gender === 'Male'
                ? $faker->randomElement($maleFirstNames)
                : $faker->randomElement($femaleFirstNames);
            $lastName  = $faker->randomElement($surnames);
            $barangay  = $faker->randomElement($barangays);

            $issued = $faker->dateTimeBetween('-5 years', '-1 month');
            $expiry = (clone $issued)->modify('+5 years');

            $licenseNumber = sprintf(
                '%s%02d-%02d-%06d',
                $faker->randomElement(['N', 'P']),
                random_int(1, 15),
                (int) date('y', $issued->getTimestamp()),
                random_int(100000, 999999)
            );

            Violator::firstOrCreate(
                ['license_number' => $licenseNumber],
                [
                    'lgu_id'               => $lguId,
                    'first_name'           => $firstName,
                    'middle_name'          => $faker->randomElement($surnames),
                    'last_name'            => $lastName,
                    'date_of_birth'        => $faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
                    'place_of_birth'       => $faker->randomElement(['Balamban, Cebu', 'Cebu City', 'Toledo City', 'Asturias, Cebu', 'Tuburan, Cebu']),
                    'gender'               => $gender,
                    'civil_status'         => $faker->randomElement($civilStatuses),
                    'permanent_address'    => 'Purok ' . random_int(1, 7) . ", Brgy. {$barangay}, Balamban, Cebu",
                    'height'               => (string) random_int(150, 185),
                    'weight'               => (string) random_int(45, 95),
                    'blood_type'           => $faker->randomElement($bloodTypes),
                    'email'                => Str::lower(Str::slug("{$firstName}.{$lastName}", '.') . random_int(1, 999) . '@email.com'),
                    'contact_number'       => '09' . random_int(100000000, 999999999),
                    'license_type'         => $faker->randomElement($licenseTypes),
                    'license_restriction'  => $faker->randomElement($restrictionPool),
                    'license_issued_date'  => $issued->format('Y-m-d'),
                    'license_expiry_date'  => $expiry->format('Y-m-d'),
                ]
            );

            $created++;
        }

        $this->command?->info("Seeded {$created} Balamban motorists.");
    }
}
