<?php

namespace App\Services\StockServices\Prices;

use App\Repositories\StockPricesRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StockDataApiService
{
    protected $apiToken;
    protected $baseUrl;

    public function __construct(
        private StockPricesRepository $stockPricesRepo
    )
    {
        $this->apiToken = config('services.stock_api.token');
        $this->baseUrl = 'https://api.stockdata.org/v1/data/eod';
    }

    public function fetchStockPricesFromStockDataApi(string $symbol)
    {
        try {
            $response = Http::get($this->baseUrl, [
                'symbols'    => $symbol,
                'api_token'  => $this->apiToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

        } catch (\Exception $e) {
            Log::error(" {$symbol}: " . $e->getMessage());
            return [];
        }
    }

    public function storeStockPricesData(string $symbol, array $dataDTOs): void
    {
        if (empty($dataDTOs)) {
            return;
        }
    
        // DTO to array
        $records = array_map(function($dto) use ($symbol) {
            return [
                'symbol' => $symbol,
                'date' => $dto->date,
                'open' => $dto->open,
                'high' => $dto->high,
                'low' => $dto->low,
                'close' => $dto->close,
                'volume' => $dto->volume,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }, $dataDTOs);

        $this->stockPricesRepo->storeStockPricesChunked($records);
    
        Log::info("Historical data stored, records: " . count($dataDTOs));
    }
}
