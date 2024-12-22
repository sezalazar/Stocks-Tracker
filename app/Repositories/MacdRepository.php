<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class MacdRepository
{
    public function getLatestMacdForTickers(array $tickers, string $timespan = 'day')
    {
        $subQuery = DB::table('macd_data')
            ->select('symbol', DB::raw('MAX(data_timestamp) as latest_timestamp'))
            ->where('timespan', $timespan)
            ->whereIn('symbol', $tickers)
            ->groupBy('symbol');

        return DB::table('macd_data')
            ->joinSub($subQuery, 'latest_macd', function ($join) {
                $join->on('macd_data.symbol', '=', 'latest_macd.symbol')
                     ->on('macd_data.data_timestamp', '=', 'latest_macd.latest_timestamp');
            })
            ->select('macd_data.symbol', 'macd_data.value', 'macd_data.signal', 'macd_data.histogram', 'macd_data.data_timestamp')
            ->get()
            ->keyBy('symbol');
    }

    public function getMacdData(string $symbol, string $timespan = 'day', int $limit = 10)
    {
        return DB::table('macd_data')
            ->where('symbol', $symbol)
            ->where('timespan', $timespan)
            ->orderBy('data_timestamp', 'desc')
            ->take($limit)
            ->get();
    }
}
