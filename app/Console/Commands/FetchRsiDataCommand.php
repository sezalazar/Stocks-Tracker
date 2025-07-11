<?php

namespace App\Console\Commands;

use App\Services\StockServices\Indicators\RsiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchRsiDataCommand extends Command
{
    protected $signature = 'rsi:fetch';
    protected $description = 'Fetch RSI data (and follow next_url if necessary)';

    protected array $tickers;
    protected array $timespans = ['day'];

    public function __construct(
        private RsiService $rsiService
    )
    {
        parent::__construct();
        $this->tickers = config('tickers.spy');
    }

    public function handle()
    {
        foreach ($this->tickers as $ticker) {
            Log::info("Fetching RSI data for ticker: {$ticker}");
            $this->info("Fetching RSI data for ticker: {$ticker}");

            foreach ($this->timespans as $timespan) {
                $this->rsiService->fetchRsiDataRecursive($ticker, $timespan);
            }
            sleep(14); // Avoid hitting the API rate limit
        }

        $this->info('RSI data fetched and stored (including next_url) successfully.');
        return Command::SUCCESS;
    }
}
