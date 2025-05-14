<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed dengan data default
        $categories = [
            'Biaya Gaji',
            'Biaya Listrik',
            'Biaya PDAM',
            'Biaya Rumah Tangga',
            'Biaya Operasional',
            'Biaya Transportasi',
            'Biaya Perawatan',
            'Biaya Administrasi',
            'Biaya Sewa',
        ];

        foreach ($categories as $category) {
            DB::table('expense_categories')->insert([
                'name' => $category,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('expense_categories');
    }
}
