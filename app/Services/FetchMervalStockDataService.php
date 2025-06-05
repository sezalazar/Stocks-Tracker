<?php
// app/Services/FetchMervalStockDataService.php

namespace App\Services;

use App\Services\MervalServices\Matriz\MatrizAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Cookie\CookieJar;
use Carbon\Carbon;

class FetchMervalStockDataService
{
    protected string $apiUrlPattern;
    protected MatrizAuthService $auth;

    public function __construct(MatrizAuthService $auth)
    {
        $this->auth          = $auth;
        $this->apiUrlPattern = config('services.merval_api.base_url');
    }

    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        $jar = $this->auth->getCookieJar();

        return Http::withOptions(['cookies' => $jar])
                   ->withHeaders([
                       'Accept'     => 'application/json, text/plain, */*',
                       'User-Agent' => 'Laravel-HTTP-Client',
                   ]);
    }

    /**
     * Fetch stock data for a given ticker from the Merval API.
     *
     * @param string $ticker The stock ticker symbol.
     * @return array|null The stock data or null on failure.
     */
    public function fetchData(string $ticker): ?array
    {
        try {
            $from = '2024-01-01T00:00:00.000Z';
            $to   = Carbon::now()->format('Y-m-d') . 'T00:00:00.000Z';
            $url  = sprintf($this->apiUrlPattern, $ticker, $from, $to, time());

            Log::info("[Fetch] GET {$url}");
            $resp = $this->client()->get($url);

            if ($resp->successful()) {
                return $resp->json();
            }

            if (in_array($resp->status(), [401,403])) {
                Log::warning("[Fetch] Session expired, cleaning cache.");
                \Illuminate\Support\Facades\Cache::forget('matriz_session_cookies');
            }
        } catch (\Throwable $e) {
            Log::error("[Fetch] Error fetching {$ticker}: {$e->getMessage()}");
        }

        return null;
    }
}
