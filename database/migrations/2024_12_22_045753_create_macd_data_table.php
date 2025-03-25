<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('macd_data', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('timespan');
            $table->timestamp('data_timestamp');
            $table->date('date_value');
            $table->float('value');
            $table->float('signal');
            $table->float('histogram');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('macd_data');
    }
};
