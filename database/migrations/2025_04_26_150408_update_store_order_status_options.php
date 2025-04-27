<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStoreOrderStatusOptions extends Migration
{
    public function up()
    {
        // Perbarui nilai default dan komentar untuk kolom status
        DB::statement("ALTER TABLE store_orders MODIFY COLUMN status ENUM('pending', 'confirmed_by_admin', 'forwarded_to_warehouse', 'shipped', 'delivered', 'completed') NOT NULL DEFAULT 'pending' COMMENT 'Status pesanan: pending/confirmed_by_admin/forwarded_to_warehouse/shipped/delivered/completed'");
    }

    public function down()
    {
        // Kembalikan ke nilai default dan komentar sebelumnya
        DB::statement("ALTER TABLE store_orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'shipped', 'completed') NOT NULL DEFAULT 'pending' COMMENT 'Status pesanan: pending/confirmed/shipped/completed'");
    }
}
