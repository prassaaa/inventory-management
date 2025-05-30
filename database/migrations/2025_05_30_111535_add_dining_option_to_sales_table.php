<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('dining_option', ['makan_di_tempat', 'dibawa_pulang'])
                  ->default('dibawa_pulang')
                  ->after('payment_type')
                  ->comment('Pilihan makan di tempat atau dibawa pulang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('dining_option');
        });
    }
};
