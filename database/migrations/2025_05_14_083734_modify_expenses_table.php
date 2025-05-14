<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyExpensesTable extends Migration
{
    public function up()
    {
        // Buat tabel sementara untuk menyimpan data
        Schema::create('expenses_temp', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('category');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Salin data pengeluaran lama ke tabel sementara
        DB::statement('INSERT INTO expenses_temp SELECT * FROM expenses');

        // Drop tabel lama
        Schema::dropIfExists('expenses');

        // Buat tabel baru dengan struktur yang dimodifikasi
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Migrasi data lama ke tabel baru
        $tempData = DB::table('expenses_temp')->get();

        foreach ($tempData as $item) {
            // Cari ID kategori yang sesuai atau buat baru jika tidak ada
            $category = DB::table('expense_categories')->where('name', $item->category)->first();

            if (!$category) {
                $categoryId = DB::table('expense_categories')->insertGetId([
                    'name' => $item->category,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $categoryId = $category->id;
            }

            // Masukkan data pengeluaran dengan kategori baru
            DB::table('expenses')->insert([
                'date' => $item->date,
                'category_id' => $categoryId,
                'amount' => $item->amount,
                'description' => $item->description,
                'store_id' => $item->store_id,
                'created_by' => $item->created_by,
                'updated_by' => $item->updated_by,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }

        // Hapus tabel sementara
        Schema::dropIfExists('expenses_temp');
    }

    public function down()
    {
        // Tidak diimplementasikan roll-back karena kompleks
    }
}
