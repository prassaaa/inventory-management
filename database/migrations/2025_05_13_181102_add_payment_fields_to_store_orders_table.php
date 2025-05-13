<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('store_orders', 'payment_type')) {
                $table->string('payment_type')->default('cash')->after('status');
            }
            if (!Schema::hasColumn('store_orders', 'due_date')) {
                $table->date('due_date')->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('store_orders', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'due_date', 'total_amount']);
        });
    }
}
