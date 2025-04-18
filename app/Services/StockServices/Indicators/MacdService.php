<?php

namespace App\Services\StockServices\Indicators;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Repositories\MacdRepository;

class MacdService
{
    protected $apiKey;
    protected $rsiDataUrl;
    protected $macdDataUrl;

    public function __construct(private MacdRepository $macdRepository)
    {
        $this->apiKey = config('services.polygon_api.token');
        $this->rsiDataUrl = 'https://api.polygon.io/v1/indicators/rsi/';
        $this->macdDataUrl = 'https://api.polygon.io/v1/indicators/macd/';
    }
    
    public function fetchMacdDataRecursiveAndStore(string $symbol, string $timespan = 'day'): void
    {
        $lastSaved = $this->getLastSavedMacdTimestamp($symbol, $timespan);
        $dateString = $lastSaved?->toDateString() ?? '2000-01-01';

        $url = $this->macdDataUrl . $symbol;
        $queryParams = [
            'timestamp.gt'   => $dateString,
            'timespan'       => $timespan,
            'adjusted'       => 'true',
            'short_window'   => 12,
            'long_window'    => 26,
            'signal_window'  => 9,
            'series_type'    => 'close',
            'order'          => 'desc',
            'limit'          => 300,
            'apiKey'         => $this->apiKey,
        ];
        $this->fetchMacdDataPage($url, $queryParams, $symbol, $timespan);
    }

    private function fetchMacdDataPage(string $baseUrl, array $queryParams, string $symbol, string $timespan): void {
        try {
            $response = Http::get($baseUrl, $queryParams);

            if (!$response->successful()) {
                return;
            }

            $data = $response->json();
            if (empty($data['results']['values'])) {
                return;
            }

            $this->storeMacdData($data['results']['values'], $symbol, $timespan);

            if (!empty($data['next_url'])) {
                sleep(18);

                $nextUrl = $data['next_url'];
                $parsedUrl   = parse_url($nextUrl);
                $baseNextUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'].$parsedUrl['path'];

                parse_str($parsedUrl['query'] ?? '', $nextQuery);
                $nextQuery['apiKey'] = $this->apiKey;

                Log::info("Following MACD next_url for {$symbol} => {$baseNextUrl}");

                $this->fetchMacdDataPage($baseNextUrl, $nextQuery, $symbol, $timespan);
            }
        } catch (\Exception $e) {
            Log::error("Error fetching MACD data for {$symbol}: " . $e->getMessage());
        }
    }

    private function storeMacdData(array $values, string $symbol, string $timespan): void
    {
        $existingTimestamps = $this->macdRepository
            ->getMacdData($symbol, $timespan)
            ->pluck('data_timestamp')
            ->map(fn($t) => Carbon::parse($t)->toDateTimeString())
            ->toArray();

        $collectValues = collect($values);
        $newData = $collectValues->filter(function ($item) use ($existingTimestamps) {
            $ts = Carbon::createFromTimestampMs($item['timestamp'])->toDateTimeString();
            return !in_array($ts, $existingTimestamps);
        });

        $insertData = $newData->map(function ($item) use ($symbol, $timespan) {
            $carbonDate = Carbon::createFromTimestampMs($item['timestamp']);
            return [
                'symbol'         => $symbol,
                'timespan'       => $timespan,
                'data_timestamp' => $carbonDate,
                'date_value'     => $carbonDate->toDateString(),
                'value'          => $item['value'],
                'signal'         => $item['signal'],
                'histogram'      => $item['histogram'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        })->toArray();

        if (!empty($insertData)) {
            DB::table('macd_data')->insert($insertData);
            Log::info("Inserted ".count($insertData)." new MACD rows for {$symbol}, timespan={$timespan}");
        } else {
            Log::info("No new MACD data to insert for {$symbol}, timespan={$timespan}");
        }
    }

    private function getLastSavedMacdTimestamp(string $symbol, string $timespan)
    {
        $row = DB::table('macd_data')
            ->where('symbol', $symbol)
            ->where('timespan', $timespan)
            ->orderBy('data_timestamp', 'desc')
            ->first();

        return $row ? Carbon::parse($row->data_timestamp) : null;
    }


}
