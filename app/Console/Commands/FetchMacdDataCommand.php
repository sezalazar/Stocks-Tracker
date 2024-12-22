<?php

namespace App\Console\Commands;

use App\Services\PolygonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchMacdDataCommand extends Command
{
    protected $signature = 'macd:fetch';
    protected $description = 'Fetch MACD data for multiple tickers';

    protected array $tickers;
    protected array $timespans = ['day'];

    public function __construct(private PolygonService $polygonService)
    {
        parent::__construct();
        $this->tickers = config('tickers.list');
    }

    public function handle()
    {
        foreach ($this->tickers as $ticker) {
            Log::info("Fetching MACD data for ticker: {$ticker}");
            $this->info("Fetching MACD data for ticker: {$ticker}");
    
            foreach ($this->timespans as $timespan) {
                $data = $this->polygonService->fetchMacdData($ticker, $timespan);
    
                if (!empty($data['results']['values'])) {
                    Log::info("New MACD data stored for ticker: {$ticker}, timespan: {$timespan}");
                } else {
                    Log::info("No new MACD data to store for ticker: {$ticker}, timespan: {$timespan}");
                }
    
                sleep(20);
            }
        }
    
        $this->info('MACD data fetched and stored successfully.');
        return Command::SUCCESS;
    }
}
