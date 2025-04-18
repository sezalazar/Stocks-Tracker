<?php

namespace App\Http\Controllers;

use App\Services\OptionsServices\MarketDataService;
use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function __construct(private MarketDataService $marketDataService)
    {
    }

    public function processMarketData(Request $request)
    {
        $request->validate([
            'market_data' => 'required|string',
        ]);

        $results = $this->marketDataService->process($request->input('market_data'));
        return inertia('Options/OptionsTab', $results);
    }

    public function showForm()
    {
        return inertia('Options/OptionsForm');
    }
}
