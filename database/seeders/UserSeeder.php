<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{


    public function run(): void
    {
        User::truncate();
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@ensam-casa.ma',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Professor Hassan',
            'email'    => 'prof@ensam-casa.ma',
            'password' => Hash::make('password'),
            'role'     => 'professor',
        ]);
        User::create([
            'name' => 'Youssef Alami',
            'email' => 'student@ensam-casa.ma',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
    }
}
