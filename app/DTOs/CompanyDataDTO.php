<?php

namespace App\DTOs;

use Illuminate\Support\Facades\Storage;

class CompanyDataDTO
{
    public function __construct(
        public string $ticker,
        public string $name,
        public string $description,
        public ?float $marketCap,
        public ?string $homepageUrl = null,
        public ?string $logoUrl = null,
        public ?string $iconUrl = null,
    ) {}

    public static function fromApi(array $apiData): self
    {
        return new self(
            ticker: $apiData['ticker'] ?? '',
            name: $apiData['name'] ?? '',
            description: $apiData['description'] ?? '',
            marketCap: $apiData['market_cap'] ?? null,
            homepageUrl: $apiData['homepage_url'] ?? null,
            logoUrl: $apiData['branding']['logo_url'] ?? null,
            iconUrl: $apiData['branding']['icon_url'] ?? null
        );
    }

    public static function fromDb(array $dbData): self
    {
        $logoPath = $dbData['logo_path'] ?? null;
        $iconPath = $dbData['icon_path'] ?? null;
    
        return new self(
            ticker: $dbData['ticker'] ?? '',
            name: $dbData['name'] ?? '',
            description: $dbData['description'] ?? '',
            marketCap: $dbData['market_cap'] ?? null,
            homepageUrl: $dbData['homepage_url'] ?? null,
            logoUrl: $logoPath ? asset($logoPath) : null,
            iconUrl: $iconPath ? asset($iconPath) : null
        );
    }

    public function toArray(): array
    {
        return [
            'results' => [
                'ticker' => $this->ticker,
                'name' => $this->name,
                'description' => $this->description,
                'market_cap' => $this->marketCap,
                'homepage_url' => $this->homepageUrl,
                'logo_url' => $this->logoUrl,
                'icon_url' => $this->iconUrl,
            ],
        ];
    }
}
