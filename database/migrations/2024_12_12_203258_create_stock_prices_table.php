<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPricesTable extends Migration
{
    public function up()
    {
        Schema::create('stock_prices', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 10);
            $table->date('date');
            $table->decimal('open', 10, 4)->nullable();
            $table->decimal('high', 10, 4)->nullable();
            $table->decimal('low', 10, 4)->nullable();
            $table->decimal('close', 10, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_prices');
    }
}
