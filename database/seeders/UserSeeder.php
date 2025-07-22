<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@pharmagest.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Administrateur',
            'last_name' => 'Système',
            'role' => 'admin',
            'is_active' => true
        ]);

        User::create([
            'username' => 'pharmacien1',
            'email' => 'pharmacien@pharmagest.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'role' => 'pharmacist',
            'is_active' => true
        ]);
    }
}