<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardDataService;
use App\Services\MarketServices\FearGreedIndexService;
use App\Services\BondsServices\BondService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardDataService $dashboardDataService,
        private FearGreedIndexService $fearGreedIndexService,
        private BondService $bondService
    ) {}

    public function index(Request $request, $symbol = null)
    {
        $dashboardLists = $this->dashboardDataService->getDashboardLists();

        $marketData = [
            'fearAndGreed' => $this->fearGreedIndexService->fetch(),
        ];

        $bondsTickers = config('tickers.bonds', []);
        $bondsList = [];
        foreach ($bondsTickers as $ticker) {
            $bondData = $this->bondService->getSecurityData($ticker);
            if ($bondData) {
                $bondsList[] = $bondData;
            }
        }


        $mervalTickers = config('tickers.merv', []);
        $mervalList = [];
        foreach ($mervalTickers as $ticker) {
            $latestData = DB::table('stock_prices')
                ->where('symbol', $ticker)
                ->orderBy('date', 'desc')
                ->limit(8)
                ->get()
                ->reverse()
                ->values();

            if ($latestData->count() < 2) {
                continue;
            }

            $latestRecord = $latestData->last();
            $previousRecord = $latestData->get($latestData->count() - 2);

            $price = $latestRecord->close;
            $change = $price - $previousRecord->close;
            $changePercent = ($previousRecord->close > 0) ? ($change / $previousRecord->close) * 100 : 0;

            $mervalList[] = [
                'symbol' => $ticker,
                'name' => "Acción {$ticker}",
                'price' => (float) $price,
                'change' => (float) $change,
                'changePercent' => (float) $changePercent,
                'volume' => $this->formatVolume($latestRecord->volume),
                'sector' => 'stocks',
                'chartData' => $latestData->slice(-7)->pluck('close')->map(fn($price) => (float) $price)->all(),
            ];

        }

        $optionsSessionData = session('optionsData', []);
        $underlying  = $optionsSessionData['underlying']  ?? null;
        $options     = $optionsSessionData['options']     ?? [];
        $strategies  = $optionsSessionData['strategies']  ?? [];

        $activeStock = null;
        if ($symbol) {
            $activeStock = collect($dashboardLists['stocksList'])->firstWhere('symbol', $symbol);
        }
        $activeTab = $request->query('tab');

        return Inertia::render('Dashboard', [
            'stocksList'     => $dashboardLists['stocksList'],
            'cryptoList'     => $dashboardLists['cryptoList'],
            'dashboardLists' => $dashboardLists,
            'marketData'     => $marketData,
            'bondsList'      => $bondsList,
            'mervalList'     => $mervalList,
            'underlying'     => $underlying,
            'options'        => $options,
            'strategies'     => $strategies,
            'activeTab'      => $activeTab,
            'activeStock'    => $activeStock,
        ]);
    }

    public function showCryptosTab(){
        return Inertia::render('DashboardTabs/CryptosTab');
    }

    public function showStocksTab(){
        return Inertia::render('DashboardTabs/StocksTab');
    }

    private function formatVolume($volume): string
    {
        if (!$volume) return 'N/A';
        
        if ($volume > 1000000) {
            return round($volume / 1000000, 1) . 'M';
        }
        if ($volume > 1000) {
            return round($volume / 1000, 1) . 'K';
        }
        return (string) round($volume);
    }
}