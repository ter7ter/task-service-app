<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Dispatcher User',
            'email' => 'dispatcher@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'dispatcher',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Master User',
            'email' => 'master@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'master',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Second Master',
            'email' => 'master2@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'master',
        ]);
    }
}
