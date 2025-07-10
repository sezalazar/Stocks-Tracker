<?php

namespace App\Services\BondsServices;

use App\Services\MervalServices\Matriz\MatrizAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BondService
{
    protected MatrizAuthService $authService;
    protected string $baseMatrizUrl;

    public function __construct(MatrizAuthService $authService)
    {
        $this->authService = $authService;
        $this->baseMatrizUrl = $this->authService->getBaseUrl();
    }

    public function getSecurityData(string $ticker): ?array
    {
        try {
            $cookieJar = $this->authService->getCookieJar();

            $from = Carbon::now()->subDays(15)->setTimezone('UTC')->toIso8601ZuluString();
            $to = Carbon::now()->addDay()->setTimezone('UTC')->toIso8601ZuluString();
            $ds = round(microtime(true) * 1000) . '-' . rand(100000, 999999);

            $apiUrl = "{$this->baseMatrizUrl}/api/v2/series/securities/bm_MERV_{$ticker}_24hs?resolution=D&from={$from}&to={$to}&_ds={$ds}";

            $response = Http::withOptions(['cookies' => $cookieJar])
                ->withHeaders($this->getDefaultHeaders($ticker))
                ->get($apiUrl);

            if (!$response->successful()) {
                Log::error("[BondService] Error fetching data for ticker {$ticker}: " . $response->status());
                return null;
            }

            $data = $response->json();

            if (empty($data['series']) || count($data['series']) < 2) {
                Log::warning("[BondService] Not enough data to process ticker {$ticker}.");
                return null;
            }

            return $this->processSeriesData($ticker, $data['series']);
        } catch (\Exception $e) {
            Log::error("[BondService] Exception while fetching data for ticker {$ticker}: " . $e->getMessage());
            return null;
        }
    }

    private function processSeriesData(string $ticker, array $series): array
    {
        usort($series, fn($a, $b) => strcmp($a['d'], $b['d']));

        $last7DaysData = array_slice($series, -7);
        $latestData = end($series);
        $previousData = prev($series);

        $price = $latestData['c'];
        $change = $price - $previousData['c'];
        $changePercent = ($previousData['c'] > 0) ? ($change / $previousData['c']) * 100 : 0;

        return [
            'symbol' => $ticker,
            'name' => "Bond {$ticker}",
            'price' => $price,
            'change' => $change,
            'changePercent' => $changePercent,
            'volume' => $this->formatVolume($latestData['v']),
            'sector' => 'bonds',
            'tir' => 0.0,
            'chartData' => array_column($last7DaysData, 'c'),
        ];
    }

    private function formatVolume(float $volume): string
    {
        if ($volume > 1000000) {
            return round($volume / 1000000, 1) . 'M';
        }
        if ($volume > 1000) {
            return round($volume / 1000, 1) . 'K';
        }
        return (string) round($volume);
    }

    private function getDefaultHeaders(string $ticker): array
    {
        return [
            'accept' => 'application/json, text/plain, */*',
            'referer' => "{$this->baseMatrizUrl}/security/bm_MERV_{$ticker}_24hs",
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
        ];
    }
}
