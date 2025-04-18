<?php

namespace App\Services\OptionsServices;

use App\Models\Option;
use Illuminate\Support\Collection;

class StrategyService
{
    public function calculateStraddle(float $strike, Collection $options, float $currentPrice): ?array
    {
        $call = $options->firstWhere('type', 'call');
        $put  = $options->firstWhere('type', 'put');

        if (!$call || !$put) {
            return null;
        }

        $cost = $call->last_price + $put->last_price;
        $breakeven_up = $strike + $cost;
        $breakeven_down = $strike - $cost;
        $distance_up_pct = (($breakeven_up - $currentPrice) / $currentPrice) * 100;
        $distance_down_pct = (($currentPrice - $breakeven_down) / $currentPrice) * 100;

        $available = config('options.available', 200000);
        $expectedDiff = config('options.difference', 7);
        $minDistance = min($distance_up_pct, $distance_down_pct);
        $allocation = ($minDistance <= $expectedDiff)
            ? $available
            : $available * ($expectedDiff / $minDistance);

        return [
            'strategy'         => 'Straddle',
            'strike'           => $strike,
            'cost'             => $cost,
            'breakeven_up'     => $breakeven_up,
            'breakeven_down'   => $breakeven_down,
            'distance_up_pct'  => $distance_up_pct,
            'distance_down_pct'=> $distance_down_pct,
            'allocation'       => $allocation,
        ];
    }
}
