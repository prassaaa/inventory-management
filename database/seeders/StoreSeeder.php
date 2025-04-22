<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    public function run()
    {
        $stores = [
            [
                'name' => 'Toko Pusat',
                'address' => 'Jl. Utama No. 123, Jakarta',
                'phone' => '021-5551234',
                'email' => 'tokopusat@example.com',
                'is_active' => true
            ],
            [
                'name' => 'Cabang Bandung',
                'address' => 'Jl. Asia Afrika No. 45, Bandung',
                'phone' => '022-4567890',
                'email' => 'bandung@example.com',
                'is_active' => true
            ],
            [
                'name' => 'Cabang Surabaya',
                'address' => 'Jl. Pemuda No. 78, Surabaya',
                'phone' => '031-7654321',
                'email' => 'surabaya@example.com',
                'is_active' => true
            ]
        ];

        foreach ($stores as $store) {
            Store::create($store);
        }
    }
}