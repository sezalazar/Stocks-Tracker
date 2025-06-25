<?php

namespace App\Services;

use App\Repositories\MacdRepository;
use App\Services\StockServices\Indicators\RsiService;
use App\Services\StockServices\Prices\StockAnalysisApiService;

class DashboardDataService
{
    public function __construct(
        private RsiService $rsiService,
        private MacdRepository $macdRepository,
        private StockAnalysisApiService $stockAnalysisService
    ) {}

    public function getDashboardLists(): array
    {
        return cache()->remember('dashboard_lists', now()->addMinutes(5), function () {
            $tickers = config('tickers.spy', []);
            $cryptos = config('tickers.criptoList', []);

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

            $cryptoList = [];
            foreach ($cryptos as $crypto) {
                $cryptoList[] = [
                    'symbol' => $crypto,
                    'rsi' => null,
                    'macd' => null,
                    'price' => null,
                    'changePercent' => null,
                ];
            }

            return [
                'stocksList' => $stocksList,
                'cryptoList' => $cryptoList,
            ];
        });
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