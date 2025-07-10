<?php

namespace App\Console\Commands;

use App\Models\CryptoData;
use App\Services\CryptoServices\CoinGeckoService;
use App\Services\CryptoServices\ImageDownloadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchCryptoPricesCommand extends Command
{
    protected $signature = 'cryptoprices:fetch';
    protected $description = 'Fetch top 100 cryptocurrency market data from CoinGecko API and store it.';

    public function __construct(
        private CoinGeckoService $coinGeckoService,
        private ImageDownloadService $imageDownloadService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Fetching top 100 cryptocurrencies by market cap...');
        
        try {
            $marketDataList = $this->coinGeckoService->fetchTopMarkets();
        } catch (\Exception $e) {
            $this->error('Failed to fetch market list: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Processing ' . count($marketDataList) . ' cryptocurrencies...');

        foreach ($marketDataList as $marketData) {
            $symbol = strtoupper($marketData['symbol']);
            $coinId = $marketData['id'];
            $this->info("Processing: {$symbol} ({$coinId})");

            try {
                $imagePath = $this->imageDownloadService->download($marketData['image'], $symbol);

                $additionalData = $this->coinGeckoService->fetchAdditionalCoinData($coinId);

                $fullData = array_merge($marketData, $additionalData, ['image_path' => $imagePath]);

                $this->storeDataForTimeframe($symbol, 'daily', $fullData);
                $this->storeDataForTimeframe($symbol, 'weekly', $fullData);
                $this->storeDataForTimeframe($symbol, 'monthly', $fullData);

                $this->info("Data stored for: {$symbol}");
                Log::info("Market data stored from CoinGecko API for: {$symbol}");

            } catch (\Exception $e) {
                $this->error("Failed to process data for {$symbol}: " . $e->getMessage());
                Log::error("Failed to process data for {$symbol}: " . $e->getMessage());
            }

            $this->info("Sleeping for 31 seconds to respect API rate limit...");
            sleep(31);
        }

        $this->info('Cryptocurrency data fetching completed.');
        return Command::SUCCESS;
    }

    private function storeDataForTimeframe(string $symbol, string $timeframe, array $data): void
    {
        $timeframeSpecificData = [
            'price' => $data['current_price'],
            'price_change_percentage_24h' => $data["price_change_percentage_{$this->getTimeframeSuffix($timeframe)}"] ?? null,
            'chart_data' => $this->getChartDataForTimeframe($timeframe, $data['historical_prices_30d']),
        ];

        $dailyOnlyData = [];
        if ($timeframe === 'daily') {
            $dailyOnlyData = [
                'image' => $data['image_path'],
                'market_cap_rank' => $data['market_cap_rank'],
                'market_cap' => $data['market_cap'],
                'ath' => $data['ath'],
                'ath_change_percentage' => $data['ath_change_percentage'],
                'price_change_24h' => $data['price_change_24h'],
            ];
        }

        $dataToStore = array_merge($timeframeSpecificData, $dailyOnlyData);

        CryptoData::updateOrCreate(
            ['symbol' => $symbol, 'timeframe' => $timeframe],
            $dataToStore
        );
    }

    private function getTimeframeSuffix(string $timeframe): string
    {
        return match ($timeframe) {
            'daily'   => '24h',
            'weekly'  => '7d',
            'monthly' => '30d',
        };
    }

    private function getChartDataForTimeframe(string $timeframe, array $historicalPrices): array
    {
        return match ($timeframe) {
            'daily'   => array_slice($historicalPrices, -7),
            'weekly'  => array_slice($historicalPrices, -7),
            'monthly' => $historicalPrices,
        };
    }
}
