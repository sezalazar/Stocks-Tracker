<?php

namespace App\Console\Commands;

use App\Services\StockServices\Finantials\FinancialModelingPrepService;
use App\Repositories\FinancialStatementsRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchBalanceSheetDataCommand extends Command
{
    protected $signature = 'balanceSheet:fetch';
    protected $description = 'Fetch financial statements for multiple tickers';

    protected array $tickers;

    public function __construct(
        private FinancialModelingPrepService $service,
        private FinancialStatementsRepository $repository
    ) {
        parent::__construct();
        $this->tickers = config('tickers.list');
    }

    public function handle()
    {
        foreach ($this->tickers as $ticker) {
            $this->info("Fetching financial data for: {$ticker}");

            $statements = $this->service->fetchIncomeStatement($ticker);

            if (!empty($statements)) {
                $this->repository->storeFinancialStatements($statements);
                $this->info("Data stored for: {$ticker}");
                Log::info(" Financial Statement stored for: {$ticker}");
            } else {
                $this->warn("No data found for: {$ticker}");
            }

            sleep(20);
        }

        $this->info('Financial data fetching completed.');
        return Command::SUCCESS;
    }
}
