<?php

namespace App\Services\StockServices\Prices;

use App\Repositories\StockPricesRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\StockDataTransformerService;

class StockAnalysisApiService
{
    protected string $baseUrl = 'https://stockanalysis.com/api/charts/s';

    public function __construct(
        private StockPricesRepository $stockPricesRepo,
        private StockDataTransformerService $stockDataTransformer,
    ) {}

    public function fetchStockPricesFromStockAnalysisApi(string $symbol): array
    {
        try {
            $url = "{$this->baseUrl}/{$symbol}/MAX/c";
            $response = Http::get($url);

            if ($response->successful()) {
                $json = $response->json();
                return $json['data'] ?? [];
            } else {
                Log::error("Failed to fetch data from stockanalysis for $symbol: {$response->status()}");
            }
        } catch (\Exception $e) {
            Log::error("Exception fetching data from stockanalysis for $symbol: {$e->getMessage()}");
        }

        return [];
    }


    public function fetchStockPricesFromDb(string $symbol): ?array
    {
        return $this->stockPricesRepo->getHistoricalData($symbol);
    }


    public function fetchLastTwoStockPricesFromDb(string $symbol): ?array
    {
        return $this->stockPricesRepo->fetchLastTwoStockPricesFromDb($symbol);
    }

    public function fetchLastTwoPricesForAllTickers(array $tickers): array
    {
        $data = $this->stockPricesRepo->fetchLastTwoPricesForAllTickersForSQLiteDatabase($tickers);
    
        return collect($data)->map(function ($prices, $ticker) {
            return $this->stockDataTransformer->fromDatabaseToFront($prices);
        })->toArray();
    }

    public function fetchLastTwoPricesForAllTickersNew(array $tickers): array
    {
        if (config('stockdata.db_api_source') === 'DB') {
            return $this->fetchLastTwoFromDatabase($tickers);
        } else {
            return [];
        }
    }

    private function fetchLastTwoFromDatabase(array $tickers): array
    {
        $rawData = $this->stockPricesRepo->fetchLastTwoRowsPerSymbol($tickers);

        $result = [];
        foreach ($rawData as $symbol => $rows) {
            $transformed = $this->stockDataTransformer->fromDatabaseToFront($rows);
            $result[$symbol] = $transformed;
        }

        return $result;
    }
}
