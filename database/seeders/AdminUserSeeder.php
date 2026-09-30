<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@printingpos.com',
            ],
            [
                'name' => 'Administrator',
                'password' => 'admin12345',
                'role' => 'Administrator',
                'is_active' => true,
            ]
        );
    }
}