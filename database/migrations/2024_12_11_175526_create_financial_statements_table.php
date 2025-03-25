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
            $table->decimal('revenue', 30, 8)->nullable();
            $table->decimal('cost_of_revenue', 30, 8)->nullable();
            $table->decimal('gross_profit', 30, 8)->nullable();
            $table->decimal('gross_profit_ratio', 30, 8)->nullable();
            $table->decimal('research_and_development_expenses', 30, 8)->nullable();
            $table->decimal('general_and_administrative_expenses', 30, 8)->nullable();
            $table->decimal('selling_and_marketing_expenses', 30, 8)->nullable();
            $table->decimal('selling_general_and_administrative_expenses', 30, 8)->nullable();
            $table->decimal('other_expenses', 30, 8)->nullable();
            $table->decimal('operating_expenses', 30, 8)->nullable();
            $table->decimal('cost_and_expenses', 30, 8)->nullable();
            $table->decimal('interest_income', 30, 8)->nullable();
            $table->decimal('interest_expense', 30, 8)->nullable();
            $table->decimal('depreciation_and_amortization', 30, 8)->nullable();
            $table->decimal('ebitda', 30, 8)->nullable();
            $table->decimal('ebitda_ratio', 30, 8)->nullable();
            $table->decimal('operating_income', 30, 8)->nullable();
            $table->decimal('operating_income_ratio', 30, 8)->nullable();
            $table->decimal('total_other_income_expenses_net', 30, 8)->nullable();
            $table->decimal('income_before_tax', 30, 8)->nullable();
            $table->decimal('income_before_tax_ratio', 30, 8)->nullable();
            $table->decimal('income_tax_expense', 30, 8)->nullable();
            $table->decimal('net_income', 30, 8)->nullable();
            $table->decimal('net_income_ratio', 30, 8)->nullable();
            $table->decimal('eps', 30, 8)->nullable();
            $table->decimal('eps_diluted', 30, 8)->nullable();
            $table->decimal('weighted_average_shs_out', 30, 8)->nullable();
            $table->decimal('weighted_average_shs_out_dil', 30, 8)->nullable();
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
