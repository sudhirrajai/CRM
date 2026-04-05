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

        // Create permissions grouped by module
        $modules = [
            'clients' => ['view', 'create', 'edit', 'delete'],
            'projects' => ['view', 'create', 'edit', 'delete'],
            'invoices' => ['view', 'create', 'edit', 'delete'],
            'hostings' => ['view', 'create', 'edit', 'delete'],
            'servers' => ['view', 'create', 'edit', 'delete'],
            'orders' => ['view', 'create', 'edit', 'delete'],
            'expenses' => ['view', 'create', 'edit', 'delete'],
            'expense_categories' => ['view', 'create', 'edit', 'delete'],
            'change_requests' => ['view', 'create', 'edit', 'delete'],
            'leads' => ['view', 'create', 'edit', 'delete'],
            'pipeline_stages' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'edit'],
            'settings' => ['view', 'edit'],
        ];

        $allPermissionNames = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                $allPermissionNames[] = $name;
                Permission::firstOrCreate(['name' => $name]);
            }
        }

        // Add legacy client permissions if still needed by controllers for now
        $legacyPermissions = ['view_own_invoices', 'view_own_projects', 'view_own_hostings'];
        foreach ($legacyPermissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Create roles and assign created permissions
        $roleClient = Role::firstOrCreate(['name' => 'client']);
        $roleClient->syncPermissions([
            'clients.view', 
            'projects.view', 
            'invoices.view', 
            'hostings.view', 
            'orders.view',
            'change_requests.view',
            'view_own_invoices', 
            'view_own_projects', 
            'view_own_hostings'
        ]);

        $roleStaff = Role::firstOrCreate(['name' => 'staff']);
        $roleStaff->syncPermissions([
            'clients.view', 'clients.create', 'clients.edit',
            'projects.view', 'projects.create', 'projects.edit',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'hostings.view', 'hostings.create', 'hostings.edit',
            'servers.view', 'servers.create', 'servers.edit',
            'orders.view', 'orders.create', 'orders.edit',
            'expenses.view', 'expenses.create', 'expenses.edit',
            'expense_categories.view', 'expense_categories.create', 'expense_categories.edit',
            'change_requests.view', 'change_requests.create', 'change_requests.edit', 'change_requests.delete',
            'leads.view', 'leads.create', 'leads.edit',
            'pipeline_stages.view',
            'reports.view',
        ]);

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->syncPermissions(Permission::all());
    }
}
