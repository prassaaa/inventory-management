<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyInitialBalancesTable extends Migration
{
    public function up()
    {
        // Buat tabel sementara untuk menyimpan data
        Schema::create('initial_balances_temp', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('cash_balance', 15, 2)->default(0);
            $table->decimal('bank1_balance', 15, 2)->default(0);
            $table->decimal('bank2_balance', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Salin data saldo lama ke tabel sementara
        DB::statement('INSERT INTO initial_balances_temp SELECT * FROM initial_balances');

        // Drop tabel lama
        Schema::dropIfExists('initial_balances');

        // Buat tabel baru dengan struktur yang dimodifikasi
        Schema::create('initial_balances', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('category_id')->constrained('balance_categories');
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Migrasi data lama ke tabel baru
        $tempData = DB::table('initial_balances_temp')->get();

        // Cari ID kategori
        $kasCategoryId = DB::table('balance_categories')->where('name', 'Kas')->first()->id ?? 1;
        $bankCategoryId = DB::table('balance_categories')->where('name', 'Bank')->first()->id ?? 2;
        $bank1CategoryId = DB::table('balance_categories')->where('name', 'Bank BCA')->first()->id ?? 5;
        $bank2CategoryId = DB::table('balance_categories')->where('name', 'Bank Mandiri')->first()->id ?? 6;

        foreach ($tempData as $item) {
            // Masukkan saldo kas
            if ($item->cash_balance > 0) {
                DB::table('initial_balances')->insert([
                    'date' => $item->date,
                    'category_id' => $kasCategoryId,
                    'amount' => $item->cash_balance,
                    'notes' => $item->notes . ' (Migrasi dari data lama - Kas)',
                    'store_id' => null,
                    'created_by' => $item->created_by,
                    'updated_by' => $item->updated_by,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            }

            // Masukkan saldo bank1
            if ($item->bank1_balance > 0) {
                DB::table('initial_balances')->insert([
                    'date' => $item->date,
                    'category_id' => $bank1CategoryId,
                    'amount' => $item->bank1_balance,
                    'notes' => $item->notes . ' (Migrasi dari data lama - Bank 1)',
                    'store_id' => null,
                    'created_by' => $item->created_by,
                    'updated_by' => $item->updated_by,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            }

            // Masukkan saldo bank2
            if ($item->bank2_balance > 0) {
                DB::table('initial_balances')->insert([
                    'date' => $item->date,
                    'category_id' => $bank2CategoryId,
                    'amount' => $item->bank2_balance,
                    'notes' => $item->notes . ' (Migrasi dari data lama - Bank 2)',
                    'store_id' => null,
                    'created_by' => $item->created_by,
                    'updated_by' => $item->updated_by,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            }
        }

        // Hapus tabel sementara
        Schema::dropIfExists('initial_balances_temp');
    }

    public function down()
    {
        // Tidak diimplementasikan roll-back karena kompleks
    }
}
