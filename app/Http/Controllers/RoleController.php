<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        // Group permissions by module for the UI
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'other';
        });

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        // Protect admin role from losing permissions
        if ($role->name === 'admin') {
            return back()->with('error', 'Admin role permissions cannot be modified.');
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
        ]);

        $role->syncPermissions($validated['permissions']);

        return back()->with('success', 'Role permissions updated successfully.');
    }
}
