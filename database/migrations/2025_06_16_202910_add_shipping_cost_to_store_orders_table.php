<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('total_amount');
            $table->decimal('grand_total', 12, 2)->default(0)->after('shipping_cost');
        });
    }

    public function down()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'grand_total']);
        });
    }
};
