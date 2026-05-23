<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEFAULT MYSQL CONNECTION USERS ===\n";
foreach (App\Models\User::with('roles', 'permissions')->get() as $user) {
    if ($user->is_sandbox) {
        echo "ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "Is Sandbox: ".($user->is_sandbox ? 'YES' : 'NO')."\n";
        echo "Roles: ".implode(', ', $user->roles->pluck('name')->toArray())."\n";
        echo "Permissions: ".implode(', ', $user->permissions->pluck('name')->toArray())."\n";
        echo "-------------------\n";
    }
}

// Now swap connection to sandbox
echo "\n=== SWAPPING CONNECTION TO SANDBOX ===\n";
Config::set('database.default', 'sandbox');
DB::purge('sandbox');
DB::reconnect('sandbox');

echo "=== SANDBOX CONNECTION USERS ===\n";
try {
    $sandboxUsers = DB::connection('sandbox')->table('users')->get();
    foreach ($sandboxUsers as $u) {
        echo "ID: {$u->id}\n";
        echo "Name: {$u->name}\n";
        echo "Email: {$u->email}\n";
        echo "Is Sandbox: ".($u->is_sandbox ? 'YES' : 'NO')."\n";

        // Query Spatie tables directly in sandbox SQLite
        $roles = DB::connection('sandbox')->table('roles')
            ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $u->id)
            ->pluck('name')
            ->toArray();
        echo "Roles in Sandbox: ".implode(', ', $roles)."\n";

        $perms = DB::connection('sandbox')->table('permissions')
            ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('model_has_permissions.model_id', $u->id)
            ->pluck('name')
            ->toArray();
        echo "Direct Perms in Sandbox: ".implode(', ', $perms)."\n";
        echo "-------------------\n";
    }
} catch (\Exception $e) {
    echo "Error querying sandbox: " . $e->getMessage() . "\n";
}
