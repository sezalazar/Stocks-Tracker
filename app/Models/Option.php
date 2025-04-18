<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_underlying_id',
        'type',
        'expiration',
        'strike',
        'buy_volume',
        'buy_price',
        'sell_price',
        'sell_volume',
        'last_price',
        'variation_percent',
        'nominal_volume',
        'previous_close',
        'implied_volatility_delta',
    ];

    protected $casts = [
        'option_underlying_id' => 'integer',
        'type' => 'string',
        'expiration' => 'date',
        'strike' => 'decimal:3',
        'buy_volume' => 'integer',
        'buy_price' => 'decimal:3',
        'sell_price' => 'decimal:3',
        'sell_volume' => 'integer',
        'last_price' => 'decimal:3',
        'variation_percent' => 'decimal:2',
        'nominal_volume' => 'integer',
        'previous_close' => 'decimal:3',
        'implied_volatility_delta' => 'decimal:3',
    ];

    public function underlying()
    {
        return $this->belongsTo(OptionUnderlying::class, 'option_underlying_id');
    }
}
