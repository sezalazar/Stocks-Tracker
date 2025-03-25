<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_data', function (Blueprint $table) {
            $table->id();
            $table->string('ticker')->unique();
            $table->string('name');
            $table->string('market')->nullable();
            $table->string('locale')->nullable();
            $table->string('primary_exchange')->nullable();
            $table->string('type')->nullable();
            $table->boolean('active')->default(true);
            $table->string('currency_name')->nullable();
            $table->string('cik')->nullable(); 
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('sic_description')->nullable();
            $table->string('ticker_root')->nullable();
            $table->string('homepage_url')->nullable();
            $table->integer('total_employees')->nullable();
            $table->date('list_date')->nullable();
            $table->bigInteger('share_class_shares_outstanding')->nullable(); 
            $table->bigInteger('weighted_shares_outstanding')->nullable();
            $table->integer('round_lot')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('icon_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_data');
    }
};
