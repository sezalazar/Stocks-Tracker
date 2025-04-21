<?php

namespace App\Services;

use App\Repositories\RsiRepository;
use Carbon\Carbon;

class DashboardService 
{
    public function __construct(private RsiRepository $repo) {}

    public function getDashboardData(): array 
    {
        $tickers = config('tickers.spy');

        $rsiCollection = $this->repo->getLatestRsiForTickers($tickers);

        $today = now()->startOfDay();

        $stocksList = array_map(function($ticker) use ($rsiCollection, $today) {
            $row = $rsiCollection->get($ticker);

            if (!$row) {
                return [
                    'symbol' => $ticker,
                    'rsi' => null,
                ];
            }

            $dataTimestamp = Carbon::parse($row->data_timestamp);

            if ($dataTimestamp->lt($today)) {
                return [
                    'symbol' => $ticker,
                    'rsi' => null,
                ];
            }

            return [
                'symbol' => $ticker,
                'rsi' => $row->value,
            ];
        }, $tickers);

        return $stocksList;
    }
}
