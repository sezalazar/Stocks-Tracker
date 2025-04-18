<?php

namespace App\Console\Commands;

use App\Services\StockServices\CompanyInfo\StoreCompanyDataService;
use App\Services\StockServices\CompanyInfo\PolygonStockInfoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchCompanyDataCommand extends Command
{
    protected $signature = 'companyData:fetch';
    protected $description = 'Fetch company data from Polygon API and save to database';

    public function __construct(
        private StoreCompanyDataService $storeCompanyDataService,
        private PolygonStockInfoService $polygonStockInfoService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $tickers = config('tickers.list');

        foreach ($tickers as $ticker) {
            Log::info("Fetching Company Data for ticker: {$ticker}");
            $this->info("Fetching Company Data for ticker: {$ticker}");

            $companyData = $this->polygonStockInfoService->getCompanyBasicData($ticker);

            if (!empty($companyData['results'])) {
                // Save to database, including images
                $this->storeCompanyDataService->storeCompanyData($companyData['results']);
                Log::info("Successfully fetched and saved data for ticker: {$ticker}");
            } else {
                Log::warning("No data found for ticker: {$ticker}");
            }

            Log::info("Sleeping for 15 seconds to avoid API rate limits...");
            sleep(15);
        }

        $this->info('Company data fetch completed.');
        return Command::SUCCESS;
    }
}
