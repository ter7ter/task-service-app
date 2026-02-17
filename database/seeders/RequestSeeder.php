<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Request;
use App\Models\User;

class RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $master1 = User::where('email', 'master@example.com')->first();
        $master2 = User::where('email', 'master2@example.com')->first();

        Request::create([
            'clientName' => 'Alice Smith',
            'phone' => '111-222-3333',
            'address' => '123 Main St',
            'problemText' => 'Laptop screen broken',
            'status' => 'new',
        ]);

        Request::create([
            'clientName' => 'Bob Johnson',
            'phone' => '444-555-6666',
            'address' => '456 Oak Ave',
            'problemText' => 'Internet connection issues',
            'status' => 'assigned',
            'assigned_to' => $master1->id,
        ]);

        Request::create([
            'clientName' => 'Charlie Brown',
            'phone' => '777-888-9999',
            'address' => '789 Pine Ln',
            'problemText' => 'Printer not printing',
            'status' => 'assigned',
            'assigned_to' => $master2->id,
        ]);

        Request::create([
            'clientName' => 'Diana Prince',
            'phone' => '101-202-3030',
            'address' => '101 Hero Blvd',
            'problemText' => 'Smartphone battery draining fast',
            'status' => 'in_progress',
            'assigned_to' => $master1->id,
        ]);

        Request::create([
            'clientName' => 'Eve Adams',
            'phone' => '303-404-5050',
            'address' => '303 Secret Dr',
            'problemText' => 'Desktop PC not turning on',
            'status' => 'new',
        ]);
    }
}
