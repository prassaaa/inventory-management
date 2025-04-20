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
       // 1. Foreign keys purchase_return_details
        Schema::table('purchase_return_details', function (Blueprint $table) {
            $table->foreign('purchase_return_id')->references('id')->on('purchase_returns')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');
        });

        // 2. Foreign keys shipment_details
        Schema::table('shipment_details', function (Blueprint $table) {
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');
        });

        // 3. Foreign keys store_return_details
        Schema::table('store_return_details', function (Blueprint $table) {
            $table->foreign('store_return_id')->references('id')->on('store_returns')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');
        });

        // 4. Foreign keys sale_details
        Schema::table('sale_details', function (Blueprint $table) {
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');
        });

        // 5. Foreign keys stock_adjustment_details
        Schema::table('stock_adjustment_details', function (Blueprint $table) {
            $table->foreign('stock_adjustment_id')->references('id')->on('stock_adjustments')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('stock_adjustment_details', function (Blueprint $table) {
            $table->dropForeign(['stock_adjustment_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['unit_id']);
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['unit_id']);
        });

        Schema::table('store_return_details', function (Blueprint $table) {
            $table->dropForeign(['store_return_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['unit_id']);
        });

        Schema::table('shipment_details', function (Blueprint $table) {
            $table->dropForeign(['shipment_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['unit_id']);
        });

        Schema::table('purchase_return_details', function (Blueprint $table) {
            $table->dropForeign(['purchase_return_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['unit_id']);
        });
    }
};
