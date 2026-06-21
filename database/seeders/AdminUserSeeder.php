<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'info@mustafa.com'
            ],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'phone' => '+249924535131',
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'sharif@omer.com'
            ],
            [
                'name' => 'Sharif Omer',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'phone' => '+211922307319',
            ]
        );
    }
}
