<?php

namespace App\Services\StockServices\CompanyInfo;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StoreCompanyDataService
{
    public function storeCompanyData(array $data): void
    {
        try {
            $logoPath = $this->downloadImage($data['branding']['logo_url'] ?? null, "{$data['ticker']}-logo.svg");
            $iconPath = $this->downloadImage($data['branding']['icon_url'] ?? null, "{$data['ticker']}-icon.png");

            DB::table('company_data')->updateOrInsert(
                ['ticker' => $data['ticker']],
                [
                    'name' => $data['name'] ?? null,
                    'market' => $data['market'] ?? null,
                    'locale' => $data['locale'] ?? null,
                    'primary_exchange' => $data['primary_exchange'] ?? null,
                    'type' => $data['type'] ?? null,
                    'active' => $data['active'] ?? true,
                    'currency_name' => $data['currency_name'] ?? null,
                    'cik' => $data['cik'] ?? null,
                    'market_cap' => $data['market_cap'] ?? null,
                    'description' => $data['description'] ?? null,
                    'sic_description' => $data['sic_description'] ?? null,
                    'ticker_root' => $data['ticker_root'] ?? null,
                    'homepage_url' => $data['homepage_url'] ?? null,
                    'total_employees' => $data['total_employees'] ?? null,
                    'list_date' => $data['list_date'] ?? null,
                    'share_class_shares_outstanding' => $data['share_class_shares_outstanding'] ?? null,
                    'weighted_shares_outstanding' => $data['weighted_shares_outstanding'] ?? null,
                    'round_lot' => $data['round_lot'] ?? null,
                    'logo_path' => $logoPath,
                    'icon_path' => $iconPath,
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error saving data for ticker: {$data['ticker']}. Message: " . $e->getMessage());
        }
    }

    private function downloadImage(?string $url, string $filename): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            $urlWithApiKey = $url . '?apiKey=' . config('services.polygon_api.token');
            $response = Http::get($urlWithApiKey);

            if ($response->successful()) {
                $path = "company_images/{$filename}";
                Storage::disk('public')->put($path, $response->body());
                return "storage/company_images/{$filename}";
            }
        } catch (\Exception $e) {
            Log::error("Error downloading image from URL: {$url}. Message: " . $e->getMessage());
        }

        return null;
    }
}
