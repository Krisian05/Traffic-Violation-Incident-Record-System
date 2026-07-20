<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProvinceAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'province_admin'],
            [
                'name' => 'Province Administrator',
                'email' => 'province@example.com',
                'role' => 'province_admin',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
