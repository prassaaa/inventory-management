<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Master data permissions
            'view products', 'create products', 'edit products', 'delete products',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'view stores', 'create stores', 'edit stores', 'delete stores',
            'view units', 'create units', 'edit units', 'delete units',
            
            // Transaction permissions
            'view purchases', 'create purchases', 'edit purchases', 'delete purchases',
            'view purchase returns', 'create purchase returns', 'edit purchase returns', 'delete purchase returns',
            'view store orders', 'create store orders', 'edit store orders', 'delete store orders',
            'view shipments', 'create shipments', 'edit shipments', 'delete shipments',
            'view store returns', 'create store returns', 'edit store returns', 'delete store returns',
            'view sales', 'create sales', 'edit sales', 'delete sales',
            'view expenses', 'create expenses', 'edit expenses', 'delete expenses',
            
            // Stock permissions
            'view stock warehouses', 'adjust stock warehouses',
            'view stock stores', 'adjust stock stores',
            'view stock opnames', 'create stock opnames', 'edit stock opnames', 'delete stock opnames',
            
            // Financial permissions
            'view financial reports', 'create financial journals', 'edit financial journals', 'delete financial journals',
            
            // System permissions
            'manage users', 'manage roles', 'backup database', 'restore database'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Owner role
        $ownerRole = Role::create(['name' => 'owner']);
        $ownerRole->givePermissionTo(Permission::all());
        
        // Admin Back Office role
        $adminBackOfficeRole = Role::create(['name' => 'admin_back_office']);
        $adminBackOfficeRole->givePermissionTo(Permission::all()->except(['view financial reports', 'backup database', 'restore database']));
        
        // Admin Gudang role
        $adminGudangRole = Role::create(['name' => 'admin_gudang']);
        $adminGudangRole->givePermissionTo([
            'view products', 'view categories', 'view suppliers', 'view units',
            'view purchases', 'view purchase returns',
            'view store orders', 'view shipments', 'create shipments', 'edit shipments',
            'view store returns', 'create store returns', 'edit store returns',
            'view stock warehouses', 'adjust stock warehouses',
            'view stock opnames', 'create stock opnames', 'edit stock opnames',
        ]);
        
        // Admin Store role
        $adminStoreRole = Role::create(['name' => 'admin_store']);
        $adminStoreRole->givePermissionTo([
            'view products', 'create products', 'edit products',
            'view categories', 'create categories', 'edit categories',
            'view units',
            'view store orders', 'create store orders', 'edit store orders',
            'view store returns',
            'view sales',
            'view expenses', 'create expenses', 'edit expenses',
            'view stock stores', 'adjust stock stores',
            'view stock opnames', 'create stock opnames', 'edit stock opnames',
        ]);
        
        // Kasir role
        $kasirRole = Role::create(['name' => 'kasir']);
        $kasirRole->givePermissionTo([
            'view products',
            'view sales', 'create sales',
            'view stock stores',
        ]);
    }
}