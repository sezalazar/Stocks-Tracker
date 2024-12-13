<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rsi_data', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('timespan');
            $table->timestamp('data_timestamp');
            $table->float('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsi_data');
    }
};
