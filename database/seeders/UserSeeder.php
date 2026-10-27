<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'broderick14@example.org'],
            [
                'name' => 'Broderick',
                'password' => Hash::make('password'),
                'stripe_token' => null,
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name' => 'Juan Pérez',
                'password' => Hash::make('12345678'),
                'stripe_token' => null,
                'is_admin' => false,
            ]
        );
    }
}
