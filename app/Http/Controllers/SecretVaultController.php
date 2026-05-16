<?php

namespace App\Http\Controllers;

use App\Models\Secret;
use App\Models\SecretCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class SecretVaultController extends Controller
{
    /**
     * Display the vault index page
     */
    public function index(Request $request)
    {
        Gate::authorize('secrets.view');

        $user = auth()->user();
        $userRoles = $user->getRoleNames();

        $query = Secret::where(function ($q) use ($user, $userRoles) {
                $q->where('created_by', $user->id)
                  ->orWhere(function($sub) use ($userRoles) {
                      foreach ($userRoles as $role) {
                          $sub->orWhereJsonContains('shared_with_roles', $role);
                      }
                  });
            })
            ->with('category');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by favorites
        if ($request->boolean('favorites')) {
            $query->where('is_favorite', true);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $secrets = $query->orderBy('is_favorite', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($secret) {
                return [
                    'id' => $secret->id,
                    'name' => $secret->name,
                    'type' => $secret->type,
                    'tags' => $secret->tags,
                    'url' => $secret->url,
                    'is_favorite' => $secret->is_favorite,
                    'category_id' => $secret->category_id,
                    'category' => $secret->category,
                    'created_by' => $secret->created_by,
                    'shared_with_roles' => $secret->shared_with_roles,
                    'last_accessed_at' => $secret->last_accessed_at?->diffForHumans(),
                    'created_at' => $secret->created_at->diffForHumans(),
                    'updated_at' => $secret->updated_at->diffForHumans(),
                ];
            });

        $categories = SecretCategory::where('created_by', auth()->id())
            ->withCount('secrets')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Vault/Index', [
            'secrets' => $secrets,
            'categories' => $categories,
            'roles' => \App\Models\Role::all(['id', 'name']),
            'typeConfig' => Secret::typeConfig(),
            'filters' => $request->only(['category', 'type', 'search', 'favorites']),
        ]);
    }

    /**
     * Store a new secret
     */
    public function store(Request $request)
    {
        Gate::authorize('secrets.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:password,database,email,ssh_key,api_key,command,note,custom',
            'encrypted_data' => 'required|array',
            'tags' => 'nullable|string|max:500',
            'url' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:secret_categories,id',
            'custom_fields' => 'nullable|array',
            'shared_with_roles' => 'nullable|array',
        ]);

        // For custom type, merge custom fields into encrypted_data
        $data = $validated['encrypted_data'];
        if ($validated['type'] === 'custom' && !empty($validated['custom_fields'])) {
            $data = array_merge($data, $validated['custom_fields']);
        }

        Secret::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'encrypted_data' => $data,
            'tags' => $validated['tags'] ?? null,
            'url' => $validated['url'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'shared_with_roles' => $validated['shared_with_roles'] ?? [],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('vault.index')->with('success', 'Secret stored securely.');
    }

    /**
     * Update a secret
     */
    public function update(Request $request, Secret $secret)
    {
        Gate::authorize('secrets.edit');

        // Ensure the user owns this secret
        if ($secret->created_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:password,database,email,ssh_key,api_key,command,note,custom',
            'encrypted_data' => 'required|array',
            'tags' => 'nullable|string|max:500',
            'url' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:secret_categories,id',
            'custom_fields' => 'nullable|array',
            'shared_with_roles' => 'nullable|array',
        ]);

        $data = $validated['encrypted_data'];
        if ($validated['type'] === 'custom' && !empty($validated['custom_fields'])) {
            $data = array_merge($data, $validated['custom_fields']);
        }

        $secret->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'encrypted_data' => $data,
            'tags' => $validated['tags'] ?? null,
            'url' => $validated['url'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'shared_with_roles' => $validated['shared_with_roles'] ?? [],
        ]);

        return redirect()->route('vault.index')->with('success', 'Secret updated.');
    }

    /**
     * Delete a secret
     */
    public function destroy(Secret $secret)
    {
        Gate::authorize('secrets.delete');

        if ($secret->created_by !== auth()->id()) {
            abort(403);
        }

        $secret->delete();
        return redirect()->route('vault.index')->with('success', 'Secret deleted.');
    }

    /**
     * Decrypt and return secret data (AJAX)
     */
    public function decrypt(Secret $secret)
    {
        Gate::authorize('secrets.view');

        $user = auth()->user();
        $isShared = false;
        foreach ($user->getRoleNames() as $role) {
            if (in_array($role, $secret->shared_with_roles ?? [])) {
                $isShared = true;
                break;
            }
        }

        if ($secret->created_by !== $user->id && !$isShared) {
            abort(403);
        }

        // Update last accessed
        $secret->update(['last_accessed_at' => now()]);

        return response()->json([
            'data' => $secret->encrypted_data,
        ]);
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(Secret $secret)
    {
        Gate::authorize('secrets.view');

        $user = auth()->user();
        $isShared = false;
        foreach ($user->getRoleNames() as $role) {
            if (in_array($role, $secret->shared_with_roles ?? [])) {
                $isShared = true;
                break;
            }
        }

        if ($secret->created_by !== $user->id && !$isShared) {
            abort(403);
        }

        $secret->update(['is_favorite' => !$secret->is_favorite]);

        return back()->with('success', $secret->is_favorite ? 'Added to favorites.' : 'Removed from favorites.');
    }

    /**
     * Store a new category
     */
    public function storeCategory(Request $request)
    {
        Gate::authorize('secrets.create');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        SecretCategory::create([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? 'ti-folder',
            'color' => $validated['color'] ?? '#6366f1',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Category created.');
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, SecretCategory $category)
    {
        Gate::authorize('secrets.edit');

        if ($category->created_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated.');
    }

    /**
     * Delete a category
     */
    public function destroyCategory(SecretCategory $category)
    {
        Gate::authorize('secrets.delete');

        if ($category->created_by !== auth()->id()) {
            abort(403);
        }

        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
