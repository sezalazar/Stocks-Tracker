<?php

namespace App\Services\StockServices\Indicators;

use App\Repositories\RsiRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RsiService
{
    protected $apiKey;
    protected $companyDataUrl;
    protected $rsiDataUrl;
    protected $macdDataUrl;

    public function __construct(
        private RsiRepository $rsiRepository
    )
    {
        $this->apiKey = config('services.polygon_api.token');
        $this->companyDataUrl = 'https://api.polygon.io/v3/reference/tickers/';
        $this->rsiDataUrl = 'https://api.polygon.io/v1/indicators/rsi/';
        $this->macdDataUrl = 'https://api.polygon.io/v1/indicators/macd/';
    }

    public function getLatestRsiForTickers(array $tickers): array
    {
        $collection = $this->rsiRepository->getLatestRsiForTickers($tickers);
        $result = [];

        foreach ($collection as $symbol => $row) {
            $result[$symbol] = [
                'value' => $row->value !== null ? round($row->value, 2) : null,
                'data_timestamp' => $row->data_timestamp,
            ];
        }

        return $result;
    }

    public function fetchRsiDataRecursive(string $symbol, string $timespan = 'day'): void
    {
        $lastSaved = $this->getLastSavedTimestamp($symbol, $timespan);
        $dateString = $lastSaved?->toDateString() ?? '2000-01-01';

        $url = $this->rsiDataUrl.$symbol;
        $queryParams = [
            'timestamp.gt' => $dateString,
            'timespan'     => $timespan,
            'adjusted'     => 'true',
            'window'       => 14,
            'series_type'  => 'close',
            'order'        => 'desc',
            'limit'        => 300,
            'apiKey'       => $this->apiKey,
        ];

        $this->fetchRsiDataPage($url, $queryParams, $symbol, $timespan);
    }

    private function fetchRsiDataPage(string $baseUrl, array $queryParams, string $symbol, string $timespan): void {
        try {
            $response = Http::get($baseUrl, $queryParams);

            if (!$response->successful()) {
                return;
            }

            $data = $response->json();
            if (empty($data['results']['values'])) {
                return;
            }

            $this->storeRsiData($data['results']['values'], $symbol, $timespan);

            if (!empty($data['next_url'])) {
                $nextUrl = $data['next_url'];

                $parsedUrl   = parse_url($nextUrl);
                $baseNextUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'].$parsedUrl['path'];

                parse_str($parsedUrl['query'] ?? '', $nextQuery);
                $nextQuery['apiKey'] = $this->apiKey;

                sleep(18);

                $this->fetchRsiDataPage($baseNextUrl, $nextQuery, $symbol, $timespan);
            }
        } catch (\Exception $e) {
            Log::error("Error fetching RSI data for {$symbol}: " . $e->getMessage());
        }
    }

    private function storeRsiData(array $values, string $symbol, string $timespan): void
    {
        $existingTimestamps = DB::table('rsi_data')
            ->where('symbol', $symbol)
            ->where('timespan', $timespan)
            ->pluck('data_timestamp')
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
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        })->toArray();

        if (!empty($insertData)) {
            DB::table('rsi_data')->insert($insertData);
            Log::info("Inserted ".count($insertData)." new RSI rows for {$symbol}, timespan={$timespan}");
        } else {
            Log::info("No new RSI data to insert for {$symbol}, timespan={$timespan}");
        }
    }

    private function getLastSavedTimestamp(string $symbol, string $timespan)
    {
        $row = DB::table('rsi_data')
            ->where('symbol', $symbol)
            ->where('timespan', $timespan)
            ->orderBy('data_timestamp', 'desc')
            ->first();

        return $row ? Carbon::parse($row->data_timestamp) : null;
    }  
}
