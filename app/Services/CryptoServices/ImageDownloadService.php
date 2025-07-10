<?php

namespace App\Services\CryptoServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageDownloadService
{
    public function download(string $url, string $symbol): ?string
    {
        try {
            $response = Http::get($url);

            if ($response->failed()) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $filename = Str::slug($symbol) . '.' . ($extension ?: 'png');
            $path = 'crypto_logos/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }
}
