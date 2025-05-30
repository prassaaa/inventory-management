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
        // Check if purchase_return_details table exists
        if (Schema::hasTable('purchase_return_details')) {
            Schema::table('purchase_return_details', function (Blueprint $table) {
                // Add purchase_detail_id column if it doesn't exist
                if (!Schema::hasColumn('purchase_return_details', 'purchase_detail_id')) {
                    $table->unsignedBigInteger('purchase_detail_id')->after('purchase_return_id');
                    $table->foreign('purchase_detail_id')->references('id')->on('purchase_details')->onDelete('cascade');
                }
            });
        } else {
            // Create the table if it doesn't exist
            Schema::create('purchase_return_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained()->onDelete('cascade');
                $table->foreignId('purchase_detail_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('unit_id')->constrained()->onDelete('cascade');
                $table->decimal('quantity', 10, 2);
                $table->decimal('price', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_return_details')) {
            Schema::table('purchase_return_details', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_return_details', 'purchase_detail_id')) {
                    $table->dropForeign(['purchase_detail_id']);
                    $table->dropColumn('purchase_detail_id');
                }
            });
        }
    }
};
