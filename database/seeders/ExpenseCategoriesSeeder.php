<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

class ExpenseCategoriesSeeder extends Seeder
{
    public function run()
    {
        // Timestamp saat ini untuk created_at dan updated_at
        $now = now();

        $categories = [
            [
                'name' => 'Biaya Air Kantor',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Listrik Kantor',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Telepon',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Penyusutan Bangunan',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Penyusutan Kendaraan',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Penyusutan Peralatan Kantor',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Pajak',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Transport/BBM',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya ATK',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Perjalanan Dinas',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Pemeliharaan Bangunan',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Pemeliharaan Kendaraan',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Pemeliharaan Peralatan Kantor',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Pembuat',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Administrasi Bank',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Kesejahteraan Karyawan',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Sosial',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Rumah Tangga Kantor',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Asuransi',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Pajak Penghasilan',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Pajak Bumi dan Bangunan',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Bunga Bank',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Bunga Leasing',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Biaya Lain-lain',
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        // Untuk debugging
        $this->command->info('Attempting to insert ' . count($categories) . ' expense categories');

        // Masukkan data kategori
        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }

        $this->command->info('Expense categories inserted successfully');
    }
}
