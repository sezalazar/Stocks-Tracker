<?php

namespace App\Console\Commands;

use App\Services\StockServices\Indicators\MacdService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchMacdDataCommand extends Command
{
    protected $signature = 'macd:fetch';
    protected $description = 'Fetch MACD data for multiple tickers and store it in the database.';

    protected array $tickers;
    protected array $timespans = ['day'];

    public function __construct(private MacdService $macdService)
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
                $this->macdService->fetchMacdDataRecursiveAndStore($ticker, $timespan);
                sleep(15);
            }
        }

        $this->info('MACD data fetched and stored successfully.');
        return Command::SUCCESS;
    }
}
