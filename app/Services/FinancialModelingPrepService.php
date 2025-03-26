<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FinancialModelingPrepService
{
    protected string $baseUrl = 'https://financialmodelingprep.com/api/v3';

    public function fetchIncomeStatement(string $symbol, int $limit = 120): array
    {
        try {
            $apiKey = config('services.financialmodelingprep_api.token');

            $response = Http::get("{$this->baseUrl}/income-statement/{$symbol}", [
                'limit' => $limit,
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection error for {$symbol}: " . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            Log::error("Unexpected error for {$symbol}: " . $e->getMessage());
            return [];
        }
    }
}
