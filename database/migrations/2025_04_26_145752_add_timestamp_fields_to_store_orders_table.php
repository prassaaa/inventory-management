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
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dateTime('confirmed_at')->nullable()->after('status');
            $table->dateTime('forwarded_at')->nullable()->after('confirmed_at');
            $table->dateTime('shipped_at')->nullable()->after('forwarded_at');
            $table->dateTime('delivered_at')->nullable()->after('shipped_at');
            $table->dateTime('completed_at')->nullable()->after('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            //
        });
    }
};
