<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financial_statements', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('reported_currency');
            $table->string('cik')->nullable();
            $table->date('date');
            $table->string('filling_date')->nullable();
            $table->string('accepted_date')->nullable();
            $table->string('calendar_year')->nullable();
            $table->string('period')->nullable();
            $table->bigInteger('revenue')->nullable();
            $table->bigInteger('cost_of_revenue')->nullable();
            $table->bigInteger('gross_profit')->nullable();
            $table->decimal('gross_profit_ratio', 10, 8)->nullable();
            $table->bigInteger('research_and_development_expenses')->nullable();
            $table->bigInteger('general_and_administrative_expenses')->nullable();
            $table->bigInteger('selling_and_marketing_expenses')->nullable();
            $table->bigInteger('selling_general_and_administrative_expenses')->nullable();
            $table->bigInteger('other_expenses')->nullable();
            $table->bigInteger('operating_expenses')->nullable();
            $table->bigInteger('cost_and_expenses')->nullable();
            $table->bigInteger('interest_income')->nullable();
            $table->bigInteger('interest_expense')->nullable();
            $table->bigInteger('depreciation_and_amortization')->nullable();
            $table->bigInteger('ebitda')->nullable();
            $table->decimal('ebitda_ratio', 10, 8)->nullable();
            $table->bigInteger('operating_income')->nullable();
            $table->decimal('operating_income_ratio', 10, 8)->nullable();
            $table->bigInteger('total_other_income_expenses_net')->nullable();
            $table->bigInteger('income_before_tax')->nullable();
            $table->decimal('income_before_tax_ratio', 10, 8)->nullable();
            $table->bigInteger('income_tax_expense')->nullable();
            $table->bigInteger('net_income')->nullable();
            $table->decimal('net_income_ratio', 10, 8)->nullable();
            $table->decimal('eps', 10, 8)->nullable();
            $table->decimal('eps_diluted', 10, 8)->nullable();
            $table->bigInteger('weighted_average_shs_out')->nullable();
            $table->bigInteger('weighted_average_shs_out_dil')->nullable();
            $table->string('link')->nullable();
            $table->string('final_link')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_statements');
    }
};
