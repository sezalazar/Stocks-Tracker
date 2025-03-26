<?php

namespace App\Console\Commands;

use App\Services\StockAnalysisApiService;
use App\Services\StockDataApiService;
use App\DTOs\StockDataDTO;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchStockPricesCommand extends Command
{
    protected $signature = 'stockprices:fetch';
    protected $description = 'Fetch stock prices for all tickers';

    public function __construct(
        private StockAnalysisApiService $stockAnalysisService,
        private StockDataApiService $stockDataService,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $tickers = config('tickers.list');

        foreach ($tickers as $ticker) {
            $this->info("Fetching prices data for: {$ticker}");

            $dtoMethod = 'fromStockAnalysis';
            $data = $this->stockAnalysisService->fetchStockPricesFromStockAnalysisApi($ticker);

            if (empty($data)) {

            }

            if (!empty($data)) {
                $dtos = array_map(fn($item) => StockDataDTO::$dtoMethod($item), $data);
                $this->stockDataService->storeStockPricesData($ticker, $dtos);
                $this->info("Data stored from StockAnalysis API for: {$ticker}");
                Log::info("Historical data stored from StockAnalysis API for: {$ticker}");
            } else {
                $dtoMethod = 'fromStockDataOrg';
                $data = $this->stockDataService->fetchStockPricesFromStockDataApi($ticker);
                if (!empty($data)) {
                    $dtos = array_map(fn($item) => StockDataDTO::$dtoMethod($item), $data['data']);
                    $this->stockDataService->storeStockPricesData($ticker, $dtos);
                    $this->info("Data stored from StockData API for: {$ticker}");
                    Log::info("Historical data stored from StockData API for: {$ticker}");
                } else{
                    $this->error("No data found for: {$ticker}");
                    Log::error("No data found for: {$ticker}");
                }
            }

            $this->info("Sleeping 30 seconds...");
            sleep(30);
        }

        $this->info('Stock prices data fetching completed.');
        return Command::SUCCESS;
    }
}
