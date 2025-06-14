<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_store_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->decimal('selling_price', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'store_id', 'unit_id'], 'unique_product_store_unit');
            $table->index(['store_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_store_prices');
    }
};
