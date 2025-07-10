<?php
  namespace App\Services;

use App\Services\MervalServices\Matriz\MatrizAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        return Http::withOptions([
                    'cookies'     => $this->auth->getCookieJar(),
                    'http_errors' => false,
                    'timeout'     => 20,
                ])
                ->retry(3, 200)
                ->withHeaders([
                    'accept'            => 'application/json, text/plain, */*',
                    'accept-language'   => 'es-US,es-419;q=0.9,es;q=0.8,it;q=0.7',
                    'dnt'               => '1',
                    'sec-fetch-dest'    => 'empty',
                    'sec-fetch-mode'    => 'cors',
                    'sec-fetch-site'    => 'same-origin',
                    'sec-ch-ua'         => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
                    'sec-ch-ua-mobile'  => '?0',
                    'sec-ch-ua-platform'=> '"Linux"',
                    'user-agent'        => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
                ]);
    }

    public function fetchData(string $ticker): ?array
{
    try {
        $from = '2024-01-01T00:00:00.000Z';
        $to   = Carbon::today('UTC')->format('Y-m-d') . 'T00:00:00.000Z';
        $ds   = round(microtime(true) * 1000) . '-' . rand(100000, 999999);

        $apiUrl  = sprintf(config('services.merval_api.base_url'), strtoupper($ticker), $from, $to, $ds);

        $base_url = rtrim(config('services.matriz_api.base_url'), '/');
        $referer_path = config('services.merval_api.referer_path');
        $referer = $base_url . sprintf($referer_path, strtoupper($ticker));

        Log::info("[Fetch] GET {$apiUrl}");

        $resp = $this->client()
                    ->withHeaders([
                        'referer' => $referer,
                        'priority' => 'u=1, i',
                    ])
                    ->get($apiUrl);

            if ($resp->successful()) {
                return $resp->json();
            }

            if (in_array($resp->status(), [401,403])) {
                Log::warning('[Fetch] Session expired, cleaning cache.');
                cache()->forget('matriz_session_data');
            } else {
                Log::error("[Fetch] Session expired, cleaning cache.");
            }
        } catch (\Throwable $e) {
            Log::error("[Fetch] Error fetching {$ticker}: {$e->getMessage()}");
        }

        return null;
    }
}