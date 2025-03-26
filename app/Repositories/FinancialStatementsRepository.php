<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class FinancialStatementsRepository
{

    public function getFinancialStatementsFromDb(string $symbol, int $limit = 10)
    {
        return DB::table('financial_statements')
            ->where('symbol', $symbol)
            ->orderBy('date', 'desc')
            ->take($limit)
            ->get();
    }


    // TODO: Change method to insert or update financial statements all at once
    public function storeFinancialStatements(array $statements): void
    {
        foreach ($statements as $statement) {
            DB::table('financial_statements')->updateOrInsert(
                [
                    'symbol' => $statement['symbol'],
                    'date' => $statement['date'],
                ],
                [
                    'reported_currency' => $statement['reportedCurrency'] ?? null,
                    'cik' => $statement['cik'] ?? null,
                    'filling_date' => $statement['fillingDate'] ?? null,
                    'accepted_date' => $statement['acceptedDate'] ?? null,
                    'calendar_year' => $statement['calendarYear'] ?? null,
                    'period' => $statement['period'] ?? null,
                    'revenue' => $statement['revenue'] ?? null,
                    'cost_of_revenue' => $statement['costOfRevenue'] ?? null,
                    'gross_profit' => $statement['grossProfit'] ?? null,
                    'gross_profit_ratio' => $statement['grossProfitRatio'] ?? null,
                    'research_and_development_expenses' => $statement['researchAndDevelopmentExpenses'] ?? null,
                    'general_and_administrative_expenses' => $statement['generalAndAdministrativeExpenses'] ?? null,
                    'selling_and_marketing_expenses' => $statement['sellingAndMarketingExpenses'] ?? null,
                    'selling_general_and_administrative_expenses' => $statement['sellingGeneralAndAdministrativeExpenses'] ?? null,
                    'other_expenses' => $statement['otherExpenses'] ?? null,
                    'operating_expenses' => $statement['operatingExpenses'] ?? null,
                    'cost_and_expenses' => $statement['costAndExpenses'] ?? null,
                    'interest_income' => $statement['interestIncome'] ?? null,
                    'interest_expense' => $statement['interestExpense'] ?? null,
                    'depreciation_and_amortization' => $statement['depreciationAndAmortization'] ?? null,
                    'ebitda' => $statement['ebitda'] ?? null,
                    'ebitda_ratio' => $statement['ebitdaratio'] ?? null,
                    'operating_income' => $statement['operatingIncome'] ?? null,
                    'operating_income_ratio' => $statement['operatingIncomeRatio'] ?? null,
                    'total_other_income_expenses_net' => $statement['totalOtherIncomeExpensesNet'] ?? null,
                    'income_before_tax' => $statement['incomeBeforeTax'] ?? null,
                    'income_before_tax_ratio' => $statement['incomeBeforeTaxRatio'] ?? null,
                    'income_tax_expense' => $statement['incomeTaxExpense'] ?? null,
                    'net_income' => $statement['netIncome'] ?? null,
                    'net_income_ratio' => $statement['netIncomeRatio'] ?? null,
                    'eps' => $statement['eps'] ?? null,
                    'eps_diluted' => $statement['epsdiluted'] ?? null,
                    'weighted_average_shs_out' => $statement['weightedAverageShsOut'] ?? null,
                    'weighted_average_shs_out_dil' => $statement['weightedAverageShsOutDil'] ?? null,
                    'link' => $statement['link'] ?? null,
                    'final_link' => $statement['finalLink'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
