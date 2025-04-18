<?php

namespace App\Services\StockServices\CompanyInfo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PolygonStockInfoService
{
    protected $apiKey;
    protected $companyDataUrl;

    public function __construct()
    {
        $this->apiKey = config('services.polygon_api.token');
        $this->companyDataUrl = 'https://api.polygon.io/v3/reference/tickers/';
    }

    public function getCompanyBasicData(string $symbol): array
    {
        try {
            $response = Http::get($this->companyDataUrl . $symbol, [
                'apiKey' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("{$symbol}: " . $e->getMessage());
        }

        return [];
    }
}
