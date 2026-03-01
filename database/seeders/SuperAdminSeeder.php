<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@ams.test'],
            [
                'university_id' => 'ADM001',
                'name' => 'Super Admin',
                'password' => Hash::make('Admin123'),
            ]
        );

        $admin->assignRole('Super Admin');
    }
}
