<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'contact@dihas.tech',
            'role' => 'super_admin',
            'password' => Hash::make('admin123'), // À changer en production
            'email_verified_at' => now(),
        ]);
    }
}
