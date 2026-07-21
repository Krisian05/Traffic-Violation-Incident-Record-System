<?php

namespace Database\Seeders;

use App\Models\Lgu;
use Illuminate\Database\Seeder;

class LguSeeder extends Seeder
{
    public function run(): void
    {
        $lgus = [
            ['code' => 'BAL', 'name' => 'Balamban',     'province' => 'Cebu'],
            ['code' => 'TOL', 'name' => 'Toledo',       'province' => 'Cebu'],
            ['code' => 'DAN', 'name' => 'Danao',        'province' => 'Cebu'],
            ['code' => 'CAR', 'name' => 'Carcar',       'province' => 'Cebu'],
            ['code' => 'CON', 'name' => 'Consolacion',  'province' => 'Cebu'],
        ];

        foreach ($lgus as $lgu) {
            Lgu::firstOrCreate(['code' => $lgu['code']], $lgu);
        }
    }
}
