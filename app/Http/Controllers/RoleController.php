<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage roles');
    }

    public function index()
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        $permissionGroups = $permissions->groupBy(function ($item, $key) {
            return explode(' ', $item->name)[0]; // Group by first word (e.g., "view", "create")
        });
        
        return view('roles.create', compact('permissionGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            // Gunakan metode yang lebih aman
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');
    }

    public function show(Role $role)
    {
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $allPermissions = Permission::all();
        
        $permissionGroups = $allPermissions->groupBy(function ($item, $key) {
            return explode(' ', $item->name)[0]; // Group by first word
        });
        
        return view('roles.show', compact('role', 'rolePermissions', 'permissionGroups'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        $permissionGroups = $permissions->groupBy(function ($item, $key) {
            return explode(' ', $item->name)[0]; // Group by first word
        });
        
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'permissionGroups'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id
        ]);

        $role->update(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            // Gunakan metode yang lebih aman
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        // Check if role is assigned to any users
        $userCount = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();

        if ($userCount > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete this role. It is assigned to one or more users.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully');
    }
}