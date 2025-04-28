<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePurchasesTableAddConfirmedStatus extends Migration
{
    public function up()
    {
        // Jika menggunakan MySQL dengan ENUM
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM('pending', 'confirmed', 'complete') NOT NULL DEFAULT 'pending'");
        }
        // Jika menggunakan database lain atau tidak menggunakan ENUM
        else {
            // Status tetap VARCHAR atau string, tidak perlu modifikasi schema
            // Tapi pastikan validasi di aplikasi mendukung nilai 'confirmed'
        }
    }

    public function down()
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM('pending', 'complete') NOT NULL DEFAULT 'pending'");
        }
    }
}
