<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('balance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity'])->default('asset');
            $table->text('description')->nullable();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed dengan data default
        $categories = [
            ['name' => 'Kas', 'type' => 'asset'],
            ['name' => 'Bank', 'type' => 'asset'],
            ['name' => 'Piutang', 'type' => 'asset'],
            ['name' => 'Modal', 'type' => 'equity'],
            ['name' => 'Bank BCA', 'type' => 'asset'],
            ['name' => 'Bank Mandiri', 'type' => 'asset'],
        ];

        foreach ($categories as $category) {
            DB::table('balance_categories')->insert([
                'name' => $category['name'],
                'type' => $category['type'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('balance_categories');
    }
}
