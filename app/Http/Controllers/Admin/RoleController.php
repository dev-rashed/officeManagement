<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Roles and permissions.
 *
 * Superadmin only, enforced by the `roles.manage` gate, which checks the role
 * directly rather than a grantable permission -- otherwise an admin could
 * grant themselves access to this screen and then grant themselves everything.
 */
class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:roles.manage'),
        ];
    }

    public function index()
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->ordered()
            ->get();

        $permissions = Permission::query()->ordered()->get()->groupBy('group');

        return view('pages.admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Set the whole permission list for one role in a single write.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        if (! $role->permissionsAreEditable()) {
            return response()->json([
                'message' => 'Superadmin always holds every permission and cannot be edited.',
            ], 422);
        }

        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);
        $role->touch(); // fires saved(), which clears the permission cache

        return response()->json([
            'message' => 'Permissions updated for '.$role->label.'.',
            'count' => count($data['permissions'] ?? []),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $slug = $this->uniqueSlug($data['label']);

        $role = Role::create([
            'name' => $slug,
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'is_superadmin' => false,
            'is_system' => false,
            'sort_order' => (int) Role::max('sort_order') + 1,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return response()->json([
            'message' => 'Role "'.$role->label.'" created.',
            'role' => $role,
        ], 201);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        // The slug is what users.role stores and what code compares against,
        // so it is fixed once created. The label is free to change.
        $role->update($data);

        return response()->json(['message' => 'Role updated.']);
    }

    public function destroy(Role $role)
    {
        if ($role->is_superadmin) {
            return response()->json(['message' => 'The superadmin role cannot be deleted.'], 422);
        }

        if ($role->is_system) {
            return response()->json([
                'message' => 'This is a built-in role that the application refers to by name. It can be edited but not removed.',
            ], 422);
        }

        $assigned = $role->users()->count();

        if ($assigned > 0) {
            return response()->json([
                'message' => "{$assigned} user(s) still have this role. Move them to another role first.",
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role removed.']);
    }

    private function uniqueSlug(string $label): string
    {
        $base = Str::of($label)->lower()->slug('_')->limit(45, '')->value() ?: 'role';
        $slug = $base;
        $n = 2;

        while (Role::where('name', $slug)->exists()) {
            $slug = $base.'_'.$n++;
        }

        return $slug;
    }
}
