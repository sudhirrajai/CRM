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

            // 3. Eagerly bind the authenticated user instance to the sandbox connection
            $user->setConnection('sandbox');

            // Clear Spatie permission caching to ensure it reads permissions from the sandbox SQLite file
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        return $next($request);
    }
}
