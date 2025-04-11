<?php

namespace App\Http\Controllers;

use App\Repositories\MacdRepository;
use App\Repositories\FinancialStatementsRepository;
use App\Services\RsiService;
use App\Services\StockAnalysisApiService;
use App\Services\StockDataTransformerService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private RsiService $rsiService,
        private MacdRepository $macdRepository,
        private StockAnalysisApiService $stockAnalysisService,
        private StockDataTransformerService $stockDataTransformer,
    ) {}

    public function index()
    {
        $tickers = config('tickers.list');
        $cryptos = config('tickers.criptoList');
        $rsiData = $this->rsiService->getLatestRsiForTickers($tickers);
        $macdData = $this->macdRepository->getLatestMacdForTickers($tickers);

        $stocksList = [];
        $lastTwoPrices = $this->stockAnalysisService->fetchLastTwoPricesForAllTickersNew($tickers);

        foreach ($tickers as $ticker) {
            $prices = $lastTwoPrices[$ticker] ?? [];
            $lastTwoCloses = $this->getLastTwoCloses($prices);

            $rsi = $rsiData[$ticker]['value'] ?? null;
            $macd = $macdData[$ticker]->value ?? null;

            $stocksList[] = [
                'symbol' => $ticker,
                'rsi' => $rsi,
                'macd' => $macd,
                'price' => $lastTwoCloses['lastClose'] ?? null,
                'changePercent' => $this->calculatePercentageChange($lastTwoCloses),
            ];
        }

        $cryptoList = [];
        foreach ($cryptos as $crypto) {
            $cryptoList[] = [
                'symbol'        => $crypto,
                'rsi'           => null,
                'macd'          => null,
                'price'         => null,
                'changePercent' => null,
            ];
        }

        return Inertia::render('Dashboard', [
            'cryptoList' => $cryptoList,
            'stocksList' => $stocksList,
        ]);
    }

    private function getLastTwoCloses(array $stockData): array
    {
        if (empty($stockData['data']) || count($stockData['data']) < 2) {
            return [];
        }
        $sorted = $stockData['data'];
        usort($sorted, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
        $last = end($sorted);
        $prev = prev($sorted);
        return [
            'lastClose' => $last['close'] ?? null,
            'prevClose' => $prev['close'] ?? null,
        ];
    }

    private function calculatePercentageChange(array $lastTwoCloses): ?float
    {
        if (!isset($lastTwoCloses['lastClose'], $lastTwoCloses['prevClose'])) {
            return null;
        }

        return (($lastTwoCloses['lastClose'] - $lastTwoCloses['prevClose']) / $lastTwoCloses['prevClose']) * 100;
    }
}
