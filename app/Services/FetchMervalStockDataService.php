<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchMervalStockDataService
{
    public function fetchData(string $ticker): ?array
    {
        $baseUrl = config('services.merval_api.base_url');
        $from    = '2024-01-01T00:00:00.000Z';
        $to      = Carbon::now()->format('Y-m-d') . 'T00:00:00.000Z';

        $url = sprintf(
            '%s/series/securities/bm_MERV_%s_24hs?resolution=D&from=%s&to=%s&_ds=%s',
            rtrim($baseUrl, '/'),
            $ticker,
            $from,
            $to,
            time()
        );

        try {
            $response = Http::withHeaders([
                'Cookie' => config('services.merval_api.cookie'),
            ])->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Failed to fetch Merval data for {$ticker}: {$response->status()} {$response->body()}");
        } catch (\Throwable $e) {
            Log::error("Exception fetching Merval data for {$ticker}: {$e->getMessage()}");
        }

        return null;
    }
}
