<?php

namespace App\Services;

use App\Models\CryptoData;
use App\Repositories\MacdRepository;
use App\Services\StockServices\Indicators\RsiService;
use App\Services\StockServices\Prices\StockAnalysisApiService;
use Illuminate\Support\Str;

class DashboardDataService
{
    public function __construct(
        private RsiService $rsiService,
        private MacdRepository $macdRepository,
        private StockAnalysisApiService $stockAnalysisService
    ) {}

    public function getDashboardLists(): array
    {
        // return cache()->remember('dashboard_lists', now()->addMinutes(5), function () {
            $tickers = config('tickers.spy', []);

            $rsiData = $this->rsiService->getLatestRsiForTickers($tickers);
            $macdData = $this->macdRepository->getLatestMacdForTickers($tickers);
            $lastTwoPrices = $this->stockAnalysisService->fetchLastTwoPricesForAllTickersNew($tickers);

            $stocksList = [];
            foreach ($tickers as $ticker) {
                $prices = $lastTwoPrices[$ticker] ?? [];
                $lastTwoCloses = $this->getLastTwoCloses($prices);

                $stocksList[] = [
                    'symbol' => $ticker,
                    'rsi' => $rsiData[$ticker]['value'] ?? null,
                    'macd' => $macdData[$ticker]->value ?? null,
                    'price' => $lastTwoCloses['lastClose'] ?? null,
                    'changePercent' => $this->calculatePercentageChange($lastTwoCloses),
                ];
            }

            // --- CRYPTOS logic ---
            $cryptoDataFromDb = CryptoData::all()->groupBy('symbol');
            $cryptoList = [];

            $formatData = function ($model) {
                if (!$model) {
                    return [
                        'price' => null, 'change' => null, 'changePercent' => null,
                        'rsi' => null, 'macd' => null, 'ma50' => null, 'ma200' => null,
                        'chartData' => [],
                    ];
                }
                
                $array = $model->toArray();
                $camelCasedArray = [];

                foreach ($array as $key => $value) {
                    $camelKey = Str::camel($key);
                    $camelCasedArray[$camelKey] = $value;
                }

                $numericKeys = ['price', 'change', 'changePercent', 'rsi', 'macd', 'ma50', 'ma200'];
                foreach ($numericKeys as $key) {
                    if (isset($camelCasedArray[$key])) {
                        $camelCasedArray[$key] = (float) $camelCasedArray[$key];
                    }
                }

                $camelCasedArray['chartData'] = $camelCasedArray['chartData'] ?? [];
                return $camelCasedArray;
            };

            foreach ($cryptoDataFromDb as $symbol => $timeframeData) {
                $daily = $timeframeData->firstWhere('timeframe', 'daily');

                if (!$daily) continue;

                $weekly = $timeframeData->firstWhere('timeframe', 'weekly');
                $monthly = $timeframeData->firstWhere('timeframe', 'monthly');

                $cryptoList[] = [
                    'symbol' => $symbol,
                    'price'   => (float) $daily->price,
                    'daily'   => $formatData($daily),
                    'weekly'  => $formatData($weekly),
                    'monthly' => $formatData($monthly),
                ];
            }

            return [
                'stocksList' => $stocksList,
                'cryptoList' => $cryptoList,
            ];
        // });
    }

    private function getLastTwoCloses(array $stockData): array
    {
        if (empty($stockData['data']) || count($stockData['data']) < 2) {
            return [];
        }
        $last = end($stockData['data']);
        $prev = prev($stockData['data']);
        return [
            'lastClose' => $last['close'] ?? null,
            'prevClose' => $prev['close'] ?? null,
        ];
    }

    private function calculatePercentageChange(array $lastTwoCloses): ?float
    {
        if (!isset($lastTwoCloses['lastClose'], $lastTwoCloses['prevClose']) || $lastTwoCloses['prevClose'] == 0) {
            return null;
        }
        return (($lastTwoCloses['lastClose'] - $lastTwoCloses['prevClose']) / $lastTwoCloses['prevClose']) * 100;
    }
}