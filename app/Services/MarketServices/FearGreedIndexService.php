<?php

namespace App\Services\MarketServices;

use Illuminate\Support\Facades\Http;

class FearGreedIndexService
{
    protected string $host;
    protected string $key;
    protected string $endpoint = 'https://fear-and-greed-index.p.rapidapi.com/v1/fgi';

    public function __construct()
    {
        $this->host = config('services.fear_and_greed_api.host');
        $this->key  = config('services.fear_and_greed_api.key');
    }

    public function fetch(): array
    {
        return Http::withHeaders([
            'x-rapidapi-host' => $this->host,
            'x-rapidapi-key'  => $this->key,
        ])->get($this->endpoint)
            ->throw()
            ->json();
    }
}
