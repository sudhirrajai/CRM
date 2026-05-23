<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SandboxDatabase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_sandbox) {
            $sandboxPath = database_path('sandbox.sqlite');

            // 1. Swaps the default connection name and package connections for the request context first
            Config::set('database.default', 'sandbox');
            Config::set('webpush.database_connection', 'sandbox');
            DB::purge('sandbox');
            DB::reconnect('sandbox');

            // 2. Automatically create and seed the sandbox database if it doesn't exist yet
            if (!file_exists($sandboxPath)) {
                touch($sandboxPath);

                // Run migrations on the sandbox connection (default is now sandbox)
                Artisan::call('migrate', [
                    '--database' => 'sandbox',
                    '--force' => true,
                ]);

                // Run seeders to populate permissions, default roles, and settings
                Artisan::call('db:seed', [
                    '--database' => 'sandbox',
                    '--force' => true,
                ]);
            }

            // 3. Synchronize the sandbox user, their direct permissions, roles, and mappings from MySQL production to SQLite sandbox on-the-fly
            try {
                // To prevent concurrent race conditions from multiple parallel Inertia requests,
                // we check if the sandbox user record and roles already exist in SQLite. If so, we skip the sync.
                $userExists = DB::connection('sandbox')->table('users')->where('id', $user->id)->exists();
                $rolesExist = DB::connection('sandbox')->table('model_has_roles')->where('model_id', $user->id)->exists();

                if (!$userExists || !$rolesExist) {
                    // Wipe the Spatie permission tables in SQLite first to perform a clean replica copy.
                    DB::connection('sandbox')->table('model_has_permissions')->delete();
                    DB::connection('sandbox')->table('model_has_roles')->delete();
                    DB::connection('sandbox')->table('role_has_permissions')->delete();
                    DB::connection('sandbox')->table('roles')->delete();
                    DB::connection('sandbox')->table('permissions')->delete();

                    // Copy roles from mysql to sandbox sqlite
                    $roles = DB::connection('mysql')->table('roles')->get();
                    foreach ($roles as $role) {
                        DB::connection('sandbox')->table('roles')->insert((array)$role);
                    }

                    // Copy permissions from mysql to sandbox sqlite
                    $permissions = DB::connection('mysql')->table('permissions')->get();
                    foreach ($permissions as $permission) {
                        DB::connection('sandbox')->table('permissions')->insert((array)$permission);
                    }

                    // Copy role_has_permissions from mysql to sandbox sqlite
                    $roleHasPermissions = DB::connection('mysql')->table('role_has_permissions')->get();
                    foreach ($roleHasPermissions as $rhp) {
                        DB::connection('sandbox')->table('role_has_permissions')->insert((array)$rhp);
                    }

                    // Copy the sandbox user record from mysql to sandbox sqlite
                    $userRecord = DB::connection('mysql')->table('users')->where('id', $user->id)->first();
                    if ($userRecord) {
                        // Wipe any existing conflicting user records with the same ID or email in SQLite
                        DB::connection('sandbox')->table('users')->where('id', $user->id)->delete();
                        DB::connection('sandbox')->table('users')->where('email', $userRecord->email)->delete();

                        DB::connection('sandbox')->table('users')->insert((array)$userRecord);
                    }

                    // Copy the user's role mappings from mysql to sandbox sqlite
                    $userRoles = DB::connection('mysql')->table('model_has_roles')->where('model_id', $user->id)->get();
                    foreach ($userRoles as $ur) {
                        DB::connection('sandbox')->table('model_has_roles')->insert((array)$ur);
                    }

                    // Copy the user's direct permission mappings from mysql to sandbox sqlite
                    $userPermissions = DB::connection('mysql')->table('model_has_permissions')->where('model_id', $user->id)->get();
                    foreach ($userPermissions as $up) {
                        DB::connection('sandbox')->table('model_has_permissions')->insert((array)$up);
                    }
                }
            } catch (\Exception $e) {
                // Log sync error so the request doesn't crash in case of edge cases
                logger()->error('Sandbox role/permission sync failed: ' . $e->getMessage());
            }

            // 4. Eagerly bind the authenticated user instance to the sandbox connection
            $user->setConnection('sandbox');

            // Clear Spatie permission caching to ensure it reads permissions from the sandbox SQLite file
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        return $next($request);
    }
}
