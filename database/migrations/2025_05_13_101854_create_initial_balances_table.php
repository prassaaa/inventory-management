<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialBalancesTable extends Migration
{
    public function up()
    {
        Schema::create('initial_balances', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('cash_balance', 15, 2)->default(0);
            $table->decimal('bank1_balance', 15, 2)->default(0);
            $table->decimal('bank2_balance', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('initial_balances');
    }
}
