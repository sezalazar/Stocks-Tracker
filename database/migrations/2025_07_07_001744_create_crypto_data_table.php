<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_data', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('timeframe')->comment('e.g., daily, weekly, monthly');
            $table->string('image')->nullable()->comment('URL to the crypto image');
            $table->unsignedInteger('market_cap_rank')->nullable();
            $table->decimal('market_cap', 22, 2)->nullable();
            $table->decimal('ath', 18, 8)->nullable()->comment('All-Time High price');
            $table->decimal('ath_change_percentage', 18, 4)->nullable()->comment('Percentage change from ATH');
            $table->decimal('price', 18, 8)->nullable();
            $table->decimal('price_change_24h', 18, 8)->nullable();
            $table->decimal('price_change_percentage_24h', 18, 4)->nullable();
            $table->decimal('rsi', 8, 2)->nullable();
            $table->decimal('macd', 18, 8)->nullable();
            $table->decimal('ma50', 18, 8)->nullable();
            $table->decimal('ma200', 18, 8)->nullable();
            $table->json('chart_data')->nullable();
            $table->timestamps();

            $table->unique(['symbol', 'timeframe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_data');
    }
};