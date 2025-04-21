<?php

namespace App\Console\Commands;

use App\Services\StockServices\Prices\StockAnalysisApiService;
use App\Services\StockServices\Prices\StockDataApiService;
use App\Services\FetchMervalStockDataService;
use App\DTOs\StockDataDTO;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchStockPricesCommand extends Command
{
    protected $signature = 'stockprices:fetch';
    protected $description = 'Fetch stock prices for all tickers including Merval stocks';

    public function __construct(
        private StockAnalysisApiService $stockAnalysisService,
        private StockDataApiService $stockDataService,
        private FetchMervalStockDataService $mervalStockService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        // Merval Tickers
        $mervalTickers = config('tickers.merv');
        $this->info('Processing Merval Tickers...');
        
        foreach ($mervalTickers as $ticker) {
            $this->info("Processing: {$ticker}");
            $data = $this->mervalStockService->fetchData($ticker);

            if (!empty($data) && isset($data['series'])) {
                $dtos = array_map(function ($item) {
                    return StockDataDTO::fromMerv($item);
                }, $data['series']);
                
                $this->stockDataService->storeStockPricesData($ticker, $dtos);
                $this->info("Data stored from Merval API for: {$ticker}");
                Log::info("Historical data stored from Merval API for: {$ticker}");
            } else {
                $this->error("No data found for: {$ticker}");
                Log::error("No data found for: {$ticker}");
            }
            $this->info("Sleeping 3 seconds...");
            sleep(3);
        }

        // Nasdaq Nyse Tickers
        $nasdaqTickers = config('tickers.spy');
        $this->info('Processing Nasdaq tickers...');

        foreach ($nasdaqTickers as $ticker) {
            $this->info("Processing: {$ticker}");
            $dtoMethod = 'fromStockAnalysis';
            $data = $this->stockAnalysisService->fetchStockPricesFromStockAnalysisApi($ticker);

            if (!empty($data)) {
                $dtos = array_map(fn($item) => StockDataDTO::$dtoMethod($item), $data);
                $this->stockDataService->storeStockPricesData($ticker, $dtos);
                $this->info("Data stored from StockAnalysis API for: {$ticker}");
                Log::info("Historical data stored from StockAnalysis API for: {$ticker}");
            }

            if (empty($data)) {
                $dtoMethod = 'fromStockDataOrg';
                $data = $this->stockDataService->fetchStockPricesFromStockDataApi($ticker);
                if (!empty($data) && isset($data['data'])) {
                    $dtos = array_map(fn($item) => StockDataDTO::$dtoMethod($item), $data['data']);
                    $this->stockDataService->storeStockPricesData($ticker, $dtos);
                    $this->info("Data stored from StockData API for: {$ticker}");
                    Log::info("Historical data stored from StockData API for: {$ticker}");
                } else{
                    $this->error("No data found for: {$ticker}");
                    Log::error("No data found for: {$ticker}");
                }
            }
            
            $this->info("Sleeping 2 seconds...");
            sleep(2);
        }

        $this->info('Stock prices data fetching completed.');
        return Command::SUCCESS;
    }
}
