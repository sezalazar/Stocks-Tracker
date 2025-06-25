<?php

  namespace App\Http\Controllers;

  use App\Repositories\MacdRepository;
  use App\Services\DashboardDataService;
  use App\Services\MarketServices\FearGreedIndexService;
  use App\Services\OptionsServices\MarketDataService;
  use App\Services\StockServices\Indicators\RsiService;
  use App\Services\StockServices\Prices\StockAnalysisApiService;
  use App\Services\StockDataTransformerService;
  use Inertia\Inertia;

  class DashboardController extends Controller
  {
      public function __construct(
          private DashboardDataService $dashboardDataService,
          private FearGreedIndexService $fearGreedIndexService
      ) {}

      public function index()
      {
          $dashboardLists = $this->dashboardDataService->getDashboardLists();

          $marketData = [
              'fearAndGreed' => $this->fearGreedIndexService->fetch(),
          ];

          $bondsTickers = config('tickers.bonds', []);
          $bondsList = [];
          foreach ($bondsTickers as $ticker) {
              $bondsList[] = [
                  'symbol'        => $ticker,
              ];
          }

          $optionsSessionData = session('optionsData', []);
          $underlying  = $optionsSessionData['underlying']  ?? null;
          $options     = $optionsSessionData['options']     ?? [];
          $strategies  = $optionsSessionData['strategies']  ?? [];

          return Inertia::render('Dashboard', [
              'stocksList'     => $dashboardLists['stocksList'],
              'cryptoList'     => $dashboardLists['cryptoList'],
              'dashboardLists' => $dashboardLists,
              'marketData'     => $marketData,
              'bondsList'      => $bondsList,
              'underlying'     => $underlying,
              'options'        => $options,
              'strategies'     => $strategies,
              'tab'            => request()->get('tab', 'crypto'),
          ]);
      }
  }