<?php

namespace App\Services\OptionsServices;

use App\Models\OptionUnderlying;
use App\Models\Option;
use Illuminate\Support\Collection;
use App\Services\OptionsServices\MarketDataParser;
use App\Services\OptionsServices\StrategyService;

class MarketDataService
{
    public function __construct(
        protected MarketDataParser $parser,
        protected StrategyService $strategyService,
    ) {
    }

    public function process(string $marketData): array
    {
        $lines = array_map('trim', explode("\n", trim($marketData)));
        [$underlyingLines, $optionTokens] = $this->separateUnderlyingAndOptions($lines);

        $underlyingData = $this->parser->parseUnderlying($underlyingLines);

        OptionUnderlying::where('symbol', $underlyingData['ticker'])->delete();

        $underlying = OptionUnderlying::create([
            'symbol'                   => $underlyingData['ticker'],
            'buy_volume'               => $underlyingData['buy_volume'],
            'buy_price'                => $underlyingData['buy_price'],
            'sell_price'               => $underlyingData['sell_price'],
            'sell_volume'              => $underlyingData['sell_volume'],
            'last_price'               => $underlyingData['last_price'],
            'variation_percent'        => $underlyingData['variation_percent'],
            'nominal_volume'           => $underlyingData['nominal_volume'],
            'previous_close'           => $underlyingData['previous_close'],
            'implied_volatility_delta' => $underlyingData['implied_volatility_delta'],
            'data_timestamp'           => now(),
        ]);

        $this->processOptions($optionTokens, $underlying);

        $underlying->load('options');

        $strategies = $this->calculateStrategies($underlying->options, $underlying->last_price);

        return [
            'underlying' => $underlying,
            'options'    => $underlying->options,
            'strategies' => $strategies,
        ];
    }

    protected function separateUnderlyingAndOptions(array $lines): array
    {
        $underlying = [];
        $options = [];
        $foundExpiration = false;

        foreach ($lines as $line) {
            if (preg_match('/^\d{1,2}\/[A-Za-z]{3}$/', $line)) {
                $foundExpiration = true;
                $options[] = $line;
            } elseif (!$foundExpiration) {
                $underlying[] = $line;
            } else {
                $options[] = $line;
            }
        }
        return [$underlying, $options];
    }

    protected function processOptions(array $tokens, OptionUnderlying $underlying): void
    {
        $tokens = array_values(array_filter($tokens, fn($t) => trim($t) !== ''));
        $currentExpiration = null;
        $total = count($tokens);
        $i = 0;

        while ($i < $total) {
            $token = trim($tokens[$i]);
            if (preg_match('/^\d{1,2}\/[A-Za-z]{3}$/', $token)) {
                $currentExpiration = $this->parser->parseExpiration($token);
                $i++;
                continue;
            }
            $remaining = $total - $i;
            if ($remaining < 21) {
                $data = array_slice($tokens, $i);
                $data = array_pad($data, 21, '-');
                $i = $total;
            } else {
                $data = array_slice($tokens, $i, 21);
                $i += 21;
            }
            $call = [
                'vol_compra'  => $data[0],
                'compra'      => $data[1],
                'venta'       => $data[2],
                'vol_venta'   => $data[3],
                'ult'         => $data[4],
                'var_pct'     => $data[5],
                'vol_nominal' => $data[6],
                'cierre'      => $data[7],
                'vol_imp'     => $data[8],
                'delta'       => $data[9],
            ];
            $strikeToken = $data[10];
            $put = [
                'vol_compra'  => $data[11],
                'compra'      => $data[12],
                'venta'       => $data[13],
                'vol_venta'   => $data[14],
                'ult'         => $data[15],
                'var_pct'     => $data[16],
                'vol_nominal' => $data[17],
                'cierre'      => $data[18],
                'vol_imp'     => $data[19],
                'delta'       => $data[20],
            ];

            $strike = ($strikeToken !== '-') ? (float) str_replace(",", ".", $strikeToken) : null;

            if ($call['ult'] !== '-') {
                Option::create([
                    'option_underlying_id' => $underlying->id,
                    'type' => 'call',
                    'expiration' => $currentExpiration,
                    'strike' => $strike,
                    'buy_volume' => ($call['vol_compra'] !== '-') ? (int) str_replace(".", "", $call['vol_compra']) : null,
                    'buy_price' => ($call['compra'] !== '-') ? (float) str_replace(",", ".", $call['compra']) : null,
                    'sell_price' => ($call['venta'] !== '-') ? (float) str_replace(",", ".", $call['venta']) : null,
                    'sell_volume' => ($call['vol_venta'] !== '-') ? (int) str_replace(".", "", $call['vol_venta']) : null,
                    'last_price' => (float) str_replace(",", ".", $call['ult']),
                    'variation_percent' => ($call['var_pct'] !== '-' && $call['var_pct'] !== '0,00%')
                        ? (float) str_replace([",", "%"], [".", ""], $call['var_pct'])
                        : null,
                    'nominal_volume' => ($call['vol_nominal'] !== '-') ? (int) str_replace(".", "", $call['vol_nominal']) : null,
                    'previous_close' => ($call['cierre'] !== '-') ? (float) str_replace(",", ".", $call['cierre']) : null,
                    'implied_volatility_delta' => ($call['delta'] !== '-') ? (float) str_replace(",", ".", $call['delta']) : null,
                ]);
            }
            if ($put['ult'] !== '-') {
                Option::create([
                    'option_underlying_id' => $underlying->id,
                    'type' => 'put',
                    'expiration' => $currentExpiration,
                    'strike' => $strike,
                    'buy_volume' => ($put['vol_compra'] !== '-') ? (int) str_replace(".", "", $put['vol_compra']) : null,
                    'buy_price' => ($put['compra'] !== '-') ? (float) str_replace(",", ".", $put['compra']) : null,
                    'sell_price' => ($put['venta'] !== '-') ? (float) str_replace(",", ".", $put['venta']) : null,
                    'sell_volume' => ($put['vol_venta'] !== '-') ? (int) str_replace(".", "", $put['vol_venta']) : null,
                    'last_price' => (float) str_replace(",", ".", $put['ult']),
                    'variation_percent' => ($put['var_pct'] !== '-' && $put['var_pct'] !== '0,00%')
                        ? (float) str_replace([",", "%"], [".", ""], $put['var_pct'])
                        : null,
                    'nominal_volume' => ($put['vol_nominal'] !== '-') ? (int) str_replace(".", "", $put['vol_nominal']) : null,
                    'previous_close' => ($put['cierre'] !== '-') ? (float) str_replace(",", ".", $put['cierre']) : null,
                    'implied_volatility_delta' => ($put['delta'] !== '-') ? (float) str_replace(",", ".", $put['delta']) : null,
                ]);
            }
        }
    }

    protected function calculateStrategies(Collection $options, float $currentPrice): array
    {
        $strategies = [];
        $groups = $options->groupBy(function ($option) {
            return $option->expiration . '-' . $option->strike;
        });

        foreach ($groups as $group) {
            $strike = $group->first()->strike;
            $straddle = $this->strategyService->calculateStraddle($strike, $group, $currentPrice);
            if ($straddle) {
                $strategies[] = $straddle;
            }
        }
        return $strategies;
    }
}
