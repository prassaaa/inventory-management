<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage roles');
    }

    public function index()
    {
        $permissions = Permission::orderBy('name')->get();
        $groups = $permissions->groupBy(function ($item, $key) {
            return explode(' ', $item->name)[0]; // Group by first word (e.g., "view", "create")
        });

        return view('permissions.index', compact('permissions', 'groups'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully');
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id
        ]);

        $permission->update(['name' => $request->name]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully');
    }

    public function destroy(Permission $permission)
    {
        // Check if permission is associated with any roles
        $roleCount = DB::table('role_has_permissions')
            ->where('permission_id', $permission->id)
            ->count();

        if ($roleCount > 0) {
            return redirect()->route('permissions.index')
                ->with('error', 'Cannot delete this permission. It is assigned to one or more roles.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully');
    }
}