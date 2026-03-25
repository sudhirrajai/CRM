<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $currency = Currency::firstOrCreate(['code' => 'USD'], [
            'name' => 'US Dollar',
            'symbol' => '$',
            'symbol_position' => 'prefix',
            'decimal_places' => 2,
        ]);

        $currencyInr = Currency::firstOrCreate(['code' => 'INR'], [
            'name' => 'Indian Rupee',
            'symbol' => '₹',
            'symbol_position' => 'prefix',
            'decimal_places' => 0,
        ]);

        $admin = User::firstOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $staff = User::firstOrCreate(['email' => 'staff@example.com'], [
            'name' => 'Staff User',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        $clientRecord = Client::firstOrCreate(['email' => 'contact@acme.com'], [
            'name' => 'Acme Corp',
            'currency_id' => $currency->id,
            'status' => 'active'
        ]);

        $clientUser = User::firstOrCreate(['email' => 'client@acme.com'], [
            'name' => 'Client User',
            'password' => Hash::make('password'),
            'client_id' => $clientRecord->id
        ]);
        $clientUser->assignRole('client');

        $project = \App\Models\Project::firstOrCreate(['name' => 'Acme Website Redesign'], [
            'client_id' => $clientRecord->id,
            'description' => 'Full redesign of the Acme Corp corporate website using Vue.js and Laravel.',
            'status' => 'in_progress',
            'start_date' => now()->subDays(15),
            'end_date' => now()->addDays(45),
        ]);

        $invoice = \App\Models\Invoice::firstOrCreate(['invoice_number' => 'INV-2026-001'], [
            'client_id' => $clientRecord->id,
            'project_id' => $project->id,
            'currency_id' => $currency->id,
            'issue_date' => now()->subDays(5),
            'due_date' => now()->addDays(10),
            'total_amount' => 4500.00,
            'status' => 'sent',
        ]);

        $server = \App\Models\Server::firstOrCreate(['ip_address' => '104.236.10.5'], [
            'name' => 'NY-Web-01',
            'provider' => 'DigitalOcean',
            'monthly_cost' => 40.00,
            'currency_id' => $currency->id,
            'status' => 'active',
        ]);

        $hosting = \App\Models\ClientHosting::firstOrCreate(['domain' => 'acme.com'], [
            'client_id' => $clientRecord->id,
            'server_id' => $server->id,
            'currency_id' => $currency->id,
            'billing_cycle' => 'monthly',
            'price' => 25.00,
            'status' => 'active',
        ]);
    }
}
