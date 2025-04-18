<?php

namespace App\Services\OptionsServices;

use Illuminate\Support\Carbon;
use Exception;

class MarketDataParser
{
    protected array $months = [
        'Ene' => 'Jan',
        'Feb' => 'Feb',
        'Mar' => 'Mar',
        'Abr' => 'Apr',
        'May' => 'May',
        'Jun' => 'Jun',
        'Jul' => 'Jul',
        'Ago' => 'Aug',
        'Sep' => 'Sep',
        'Oct' => 'Oct',
        'Nov' => 'Nov',
        'Dic' => 'Dec',
    ];

    public function getMonthsMapping(): array
    {
        return $this->months;
    }

    public function parseUnderlying(array $lines): array
    {
        if (count($lines) < 9) {
            throw new Exception("El bloque del subyacente no tiene suficientes datos.");
        }

        return [
            'ticker'                => trim($lines[0]),
            'buy_volume'            => (int) str_replace(".", "", trim($lines[1])),
            'buy_price'             => (float) str_replace(",", ".", trim($lines[2])),
            'sell_price'            => (float) str_replace(",", ".", trim($lines[3])),
            'sell_volume'           => (int) str_replace(".", "", trim($lines[4])),
            'last_price'            => (float) str_replace(",", ".", trim($lines[5])),
            'variation_percent'     => (float) str_replace(["%", ","], ["", "."], trim($lines[6])),
            'nominal_volume'        => (int) str_replace(".", "", trim($lines[7])),
            'previous_close'        => (float) str_replace(",", ".", trim($lines[8])),
            'implied_volatility_delta' => (isset($lines[9]) && trim($lines[9]) !== '-') 
                                            ? (float) str_replace(",", ".", trim($lines[9]))
                                            : null,
        ];
    }

    public function parseExpiration(string $dateString): Carbon
    {
        if (preg_match('/(\d{1,2})\/([A-Za-z]{3})/', $dateString, $matches)) {
            $day = $matches[1];
            $monthEs = $matches[2];
            $monthEn = $this->months[$monthEs] ?? $monthEs;
            $formatted = $day . '/' . $monthEn;
            return Carbon::createFromFormat('d/M', $formatted, 'America/Argentina/Buenos_Aires')
                ->year(2025) // Año asumido
                ->startOfDay();
        }
        throw new Exception("Formato de fecha incorrecto en: {$dateString}");
    }
}
