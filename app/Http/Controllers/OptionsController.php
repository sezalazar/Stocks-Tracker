<?php

namespace App\Http\Controllers;

use App\Services\OptionsServices\MarketDataService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OptionsController extends Controller
{
    public function __construct(
        private MarketDataService $marketDataService,
    ){}

    public function processMarketData(Request $request)
    {
        $request->validate([
            'market_data' => 'required|string',
        ]);


        $results = $this->marketDataService->process($request->input('market_data'));
        return Inertia::render('Dashboard', [
            'underlying' => $results['underlying'] ?? null,
            'options' => $results['options'] ?? [],
            'strategies' => $results['strategies'] ?? [],
        ]);
    }
}
