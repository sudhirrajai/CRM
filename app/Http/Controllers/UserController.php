<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;


class UserController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        $grouped = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            if (count($parts) > 1) {
                $module = $parts[0];
                $grouped[$module][] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            }
        }

        return Inertia::render('Users/Index', [
            'users' => User::with(['roles', 'permissions'])->get(),
            'roles' => Role::all(),
            'groupedPermissions' => $grouped,
        ]);
    }

    public function create()
    {
        $permissions = Permission::all();
        $grouped = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            if (count($parts) > 1) {
                $module = $parts[0];
                $grouped[$module][] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            }
        }

        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
            'groupedPermissions' => $grouped,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'required|array',
            'permissions' => 'nullable|array',
            'is_sandbox' => 'nullable|boolean',
            'send_email' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'is_sandbox' => $request->boolean('is_sandbox'),
        ]);

        $user->assignRole($validated['roles']);
        
        if ($request->has('permissions')) {
            $user->syncPermissions($validated['permissions'] ?? []);
        }

        if ($request->boolean('send_email')) {
            Mail::to($user->email)->send(new UserCredentialsMail($user, $validated['password']));
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $permissions = Permission::all();
        $grouped = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            if (count($parts) > 1) {
                $module = $parts[0];
                $grouped[$module][] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            }
        }

        return Inertia::render('Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
            'groupedPermissions' => $grouped,
            'directPermissions' => $user->permissions->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'roles' => 'required|array',
            'permissions' => 'nullable|array',
            'is_sandbox' => 'nullable|boolean',
            'send_email' => 'nullable|boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_sandbox' => $request->boolean('is_sandbox'),
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);

            if ($request->boolean('send_email')) {
                Mail::to($user->email)->send(new UserCredentialsMail($user, $validated['password']));
            }
        }

        $user->syncRoles($validated['roles']);
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
