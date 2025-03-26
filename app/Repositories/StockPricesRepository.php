<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class StockPricesRepository
{
    public function getHistoricalData(string $symbol): ?array
    {
        $rows = DB::table('stock_prices')
            ->where('symbol', $symbol)
            ->orderBy('date', 'asc')
            ->get();

        $data = [];
        foreach ($rows as $row) {
            $timestamp = strtotime($row->date); 
            $data[] = [
                't' => $timestamp,
                'o' => (float)$row->open,
                'h' => (float)$row->high,
                'l' => (float)$row->low,
                'c' => (float)$row->close,
                'v' => (int)$row->volume
            ];
        }

        return $data;
    }


    public function fetchLastTwoStockPricesFromDb(string $symbol): ?array
    {
        $rows = DB::table('stock_prices')
            ->where('symbol', $symbol)
            ->orderBy('date', 'asc')
            ->limit(2)
            ->get();

        $data = [];
        foreach ($rows as $row) {
            $timestamp = strtotime($row->date); 
            $data[] = [
                't' => $timestamp,
                'o' => (float)$row->open,
                'h' => (float)$row->high,
                'l' => (float)$row->low,
                'c' => (float)$row->close,
                'v' => (int)$row->volume
            ];
        }

        return $data;
    }

    public function fetchLastTwoPricesForAllTickersForSQLiteDatabase(array $tickers): array
    {
        $query = DB::table('stock_prices')
            ->select('symbol', 'date', 'open', 'high', 'low', 'close', 'volume')
            ->whereIn('symbol', $tickers)
            ->orderBy('symbol')
            ->orderByDesc('date');
    
        $data = $query->get()->groupBy('symbol');
    
        return $data->map(function ($rows) {
            return $rows->map(function ($row) {
                return [
                    'date' => $row->date,
                    'open' => (float) $row->open,
                    'high' => (float) $row->high,
                    'low' => (float) $row->low,
                    'close' => (float) $row->close,
                    'volume' => (int) $row->volume,
                ];
            })->toArray();
        })->toArray();
    }


    public function fetchLastTwoPricesForAllTickersForPostgreSQL(array $tickers): array
    {
        $placeholders = implode(',', array_fill(0, count($tickers), '?'));

        $sql = "
            WITH last_two AS (
                SELECT
                    sp.*,
                    row_number() OVER (PARTITION BY sp.symbol ORDER BY sp.date DESC) AS rn
                FROM stock_prices sp
                WHERE sp.symbol IN ($placeholders)
            )
            SELECT *
            FROM last_two
            WHERE rn <= 2
            ORDER BY symbol, date DESC
        ";

        $rows = DB::select($sql, $tickers);

        $result = [];
        foreach ($rows as $row) {
            if (!isset($result[$row->symbol])) {
                $result[$row->symbol] = ['data' => []];
            }
            $result[$row->symbol]['data'][] = [
                'date'   => $row->date,
                'open'   => $row->open,
                'high'   => $row->high,
                'low'    => $row->low,
                'close'  => $row->close,
                'volume' => $row->volume,
            ];
        }

        return $result;
    }


    public function storeStockPricesChunked(array $records): void
    {
        if (empty($records)) {
            return;
        }

        $chunkSize = 500;
        $chunks = array_chunk($records, $chunkSize);

        DB::transaction(function () use ($chunks) {
            foreach ($chunks as $chunk) {
                DB::table('stock_prices')->upsert(
                    $chunk,
                    ['symbol', 'date'],  
                    ['open', 'high', 'low', 'close', 'volume', 'updated_at']
                );
            }
        });
    }

    public function fetchLastTwoRowsPerSymbol(array $tickers): array
    {
        $placeholders = implode(',', array_fill(0, count($tickers), '?'));

        $sql = "
            WITH last_two AS (
                SELECT
                    sp.*,
                    row_number() OVER (PARTITION BY sp.symbol ORDER BY sp.date DESC) AS rn
                FROM stock_prices sp
                WHERE sp.symbol IN ($placeholders)
            )
            SELECT *
            FROM last_two
            WHERE rn <= 2
            ORDER BY symbol, date DESC
        ";

        $rows = DB::select($sql, $tickers);

        $result = [];
        foreach ($rows as $row) {
            if (!isset($result[$row->symbol])) {
                $result[$row->symbol] = [];
            }
            $result[$row->symbol][] = [
                'date'   => $row->date,
                'open'   => (float) $row->open,
                'high'   => (float) $row->high,
                'low'    => (float) $row->low,
                'close'  => (float) $row->close,
                'volume' => (int) $row->volume,
            ];
        }

        return $result;
    }
    
    
}
