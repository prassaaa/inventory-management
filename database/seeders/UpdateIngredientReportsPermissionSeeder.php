<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateIngredientReportsPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create new permission if not exists
        if (!Permission::where('name', 'view ingredient reports')->exists()) {
            Permission::create(['name' => 'view ingredient reports']);
        }

        // Assign permission to roles
        $ownerRole = Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $ownerRole->givePermissionTo('view ingredient reports');
        }

        $adminBackOfficeRole = Role::where('name', 'admin_back_office')->first();
        if ($adminBackOfficeRole) {
            $adminBackOfficeRole->givePermissionTo('view ingredient reports');
        }

        $adminStoreRole = Role::where('name', 'admin_store')->first();
        if ($adminStoreRole) {
            $adminStoreRole->givePermissionTo('view ingredient reports');
        }

        // TAMBAHAN: Owner Store role
        $ownerStoreRole = Role::where('name', 'owner_store')->first();
        if ($ownerStoreRole) {
            $ownerStoreRole->givePermissionTo('view ingredient reports');
        }

        echo "✅ Permission 'view ingredient reports' berhasil ditambahkan!\n";
        echo "✅ Permission berhasil di-assign ke role: owner, admin_back_office, admin_store, owner_store\n";
    }
}
