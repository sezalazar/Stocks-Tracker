<?php

namespace App\DTOs;

class StockDataDTO
{
    public string $date;
    public float $open;
    public float $high;
    public float $low;
    public float $close;
    public int $volume;

    public function __construct(string $date, float $open, float $high, float $low, float $close, int $volume)
    {
        $this->date = $date;
        $this->open = $open;
        $this->high = $high;
        $this->low = $low;
        $this->close = $close;
        $this->volume = $volume;
    }

    /**
     * Create DTO from stockdata.org API Response
     */
    public static function fromStockDataOrg(array $item): self
    {
        return new self(
            substr($item['date'], 0, 10), // "YYYY-MM-DD"
            $item['open'],
            $item['high'],
            $item['low'],
            $item['close'],
            $item['volume']
        );
    }

    /**
     * Create DTO from stockanalysis.com API Response
     */
    public static function fromStockAnalysis(array $item): self
    {
        $date = date('Y-m-d', $item['t']);
        return new self(
            $date,
            $item['o'],
            $item['h'],
            $item['l'],
            $item['c'],
            $item['v']
        );
    }

    public static function fromMerv(array $item): self
    {
        return new self(
            substr($item['d'], 0, 10),
            $item['o'],
            $item['h'],
            $item['l'],
            $item['c'],
            (int) $item['v']
        );
    }

    public static function fromDatabase(array $item): self
    {
        return new self(
            $item['date'],
            (float) $item['open'],
            (float) $item['high'],
            (float) $item['low'],
            (float) $item['close'],
            (int) $item['volume']
        );
    }
}
