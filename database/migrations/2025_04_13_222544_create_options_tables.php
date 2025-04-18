<?php

use App\Models\Option;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('option_underlyings')) {
            Schema::create('option_underlyings', function (Blueprint $table) {
                $table->id();
                $table->string('symbol', 10);
                $table->bigInteger('buy_volume')->nullable();
                $table->decimal('buy_price', 10, 3)->nullable();
                $table->decimal('sell_price', 10, 3)->nullable();
                $table->bigInteger('sell_volume')->nullable();
                $table->decimal('last_price', 10, 3)->nullable();
                $table->decimal('variation_percent', 6, 2)->nullable();
                $table->bigInteger('nominal_volume')->nullable();
                $table->decimal('previous_close', 10, 3)->nullable();
                $table->decimal('implied_volatility_delta', 10, 3)->nullable();
                $table->timestamp('data_timestamp')->nullable();
                $table->timestamps();
    
                $table->unique(['symbol']);
            });
        }

        if(!Schema::hasTable('options')) {
            Schema::create('options', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Option::class)->constrained()->onDelete('cascade');
                $table->enum('type', ['call', 'put']);
                $table->date('expiration');
                $table->decimal('strike', 10, 3);
                $table->bigInteger('buy_volume')->nullable();
                $table->decimal('buy_price', 10, 3)->nullable();
                $table->decimal('sell_price', 10, 3)->nullable();
                $table->bigInteger('sell_volume')->nullable();
                $table->decimal('last_price', 10, 3)->nullable();
                $table->decimal('variation_percent', 6, 2)->nullable();
                $table->bigInteger('nominal_volume')->nullable();
                $table->decimal('previous_close', 10, 3)->nullable();
                $table->decimal('implied_volatility_delta', 10, 3)->nullable();
                $table->timestamps();
    
                $table->index(['option_underlying_id', 'strike', 'expiration', 'type']);
            });
        }

        if(!Schema::hasTable('option_strategies')) {
            Schema::create('option_strategies', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Option::class)->constrained()->onDelete('cascade');
                $table->string('strategy', 50);
                $table->decimal('strike', 10, 3);
                $table->decimal('cost', 10, 3);
                $table->decimal('breakeven_up', 10, 3);
                $table->decimal('breakeven_down', 10, 3);
                $table->decimal('distance_up_pct', 8, 2)->nullable();
                $table->decimal('distance_down_pct', 8, 2)->nullable();
                $table->decimal('allocation', 10, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('option_strategies');
        Schema::dropIfExists('options');
        Schema::dropIfExists('option_underlyings');
        
    }
};
