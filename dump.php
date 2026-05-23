<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ALL USERS ===\n";
foreach (App\Models\User::with('roles', 'permissions')->get() as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Is Sandbox: ".($user->is_sandbox ? 'YES' : 'NO')."\n";
    echo "Roles: ".implode(', ', $user->roles->pluck('name')->toArray())."\n";
    echo "Permissions: ".implode(', ', $user->permissions->pluck('name')->toArray())."\n";
    echo "-------------------\n";
}
