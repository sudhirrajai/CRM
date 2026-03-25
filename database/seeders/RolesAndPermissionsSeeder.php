<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view_all_clients', 'edit_all_clients', 'delete_all_clients',
            'view_all_projects', 'edit_all_projects', 'delete_all_projects',
            'view_all_invoices', 'edit_all_invoices', 'delete_all_invoices',
            'view_all_hostings', 'edit_all_hostings', 'delete_all_hostings',
            'view_all_servers', 'edit_all_servers', 'delete_all_servers',
            
            // Client specific
            'view_own_invoices', 'view_own_projects', 'view_own_hostings'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign created permissions
        $roleClient = Role::firstOrCreate(['name' => 'client']);
        $roleClient->syncPermissions(['view_own_invoices', 'view_own_projects', 'view_own_hostings']);

        $roleStaff = Role::firstOrCreate(['name' => 'staff']);
        $roleStaff->syncPermissions([
            'view_all_clients', 'edit_all_clients',
            'view_all_projects', 'edit_all_projects',
            'view_all_invoices', 'edit_all_invoices',
            'view_all_hostings', 'edit_all_hostings',
        ]);

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->syncPermissions(Permission::all());
    }
}
