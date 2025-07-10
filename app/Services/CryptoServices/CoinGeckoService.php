<?php

namespace App\Services\CryptoServices;

use Illuminate\Support\Facades\Http;
use Exception;

class CoinGeckoService
{
    protected string $baseUrl = 'https://api.coingecko.com/api/v3';

    /**
     * Obtiene las 100 principales criptomonedas por capitalización de mercado.
     *
     * @return array
     * @throws Exception
     */
    public function fetchTopMarkets(): array
    {
        $response = Http::get("{$this->baseUrl}/coins/markets", [
            'vs_currency' => 'usd',
            'order' => 'market_cap_desc',
            'per_page' => 100,
            'page' => 1,
            'sparkline' => 'false',
        ]);

        if ($response->failed()) {
            throw new Exception("Error en la API de CoinGecko (/coins/markets). Status: " . $response->status());
        }

        return $response->json();
    }

    public function fetchAdditionalCoinData(string $coinId): array
    {
        $detailsResponse = Http::get("{$this->baseUrl}/coins/{$coinId}", [
            'localization' => 'false',
            'tickers' => 'false',
            'market_data' => 'true',
            'community_data' => 'false',
            'developer_data' => 'false',
        ]);

        $historicalResponse = Http::get("{$this->baseUrl}/coins/{$coinId}/market_chart", [
            'vs_currency' => 'usd',
            'days' => '31',
            'interval' => 'daily',
        ]);

        if ($detailsResponse->failed() || $historicalResponse->failed()) {
            throw new Exception("Error al obtener datos adicionales para {$coinId}.");
        }
        
        $details = $detailsResponse->json();
        $historical = $historicalResponse->json();

        return [
            'price_change_percentage_7d' => $details['market_data']['price_change_percentage_7d_in_currency']['usd'] ?? null,
            'price_change_percentage_30d' => $details['market_data']['price_change_percentage_30d_in_currency']['usd'] ?? null,
            'historical_prices_30d' => array_column($historical['prices'], 1),
        ];
    }
}
