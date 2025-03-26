<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class RsiRepository 
{
    public function getLatestRsiForTickers(array $tickers)
    {
        $placeholders = implode(',', array_fill(0, count($tickers), '?'));
    
        $sql = "
            WITH latest_rsi AS (
                SELECT
                    r.*,
                    row_number() OVER (PARTITION BY r.symbol ORDER BY r.data_timestamp DESC) AS rn
                FROM rsi_data r
                WHERE r.symbol IN ($placeholders)
                  AND r.timespan = 'day'
            )
            SELECT symbol, value, data_timestamp
            FROM latest_rsi
            WHERE rn = 1
        ";
    
        $rows = DB::select($sql, $tickers);
        $collection = collect($rows)->keyBy('symbol');
        return $collection;
    }

    public function getRsiData(string $symbol, string $timespan = 'day', int $limit = 10)
    {
        return DB::table('rsi_data')
            ->where('symbol', $symbol)
            ->where('timespan', $timespan)
            ->orderBy('data_timestamp', 'desc')
            ->get();
    }
}
