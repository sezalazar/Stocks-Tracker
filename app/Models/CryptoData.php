<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoData extends Model
{
    use HasFactory;

    protected $table = 'crypto_data';

    protected $fillable = [
        'symbol',
        'timeframe',
        'image',
        'market_cap_rank',
        'market_cap',
        'ath',
        'ath_change_percentage',
        'price',
        'price_change_24h',
        'price_change_percentage_24h',
        'rsi',
        'macd',
        'ma50',
        'ma200',
        'chart_data',
    ];

    protected $casts = [
        'chart_data'                  => 'array',
        'price'                       => 'float',
        'market_cap'                  => 'float',
        'ath'                         => 'float',
        'ath_change_percentage'       => 'float',
        'price_change_24h'            => 'float',
        'price_change_percentage_24h' => 'float',
    ];
}
