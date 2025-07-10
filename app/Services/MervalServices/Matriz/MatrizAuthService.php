<?php

namespace App\Services\MervalServices\Matriz;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Cookie\CookieJar;

class MatrizAuthService
{
    protected string $baseMatriz;
    protected string $loginUrl;
    protected string $profileUrl;
    protected string $cacheKey = 'matriz_session_data';

    public function __construct()
    {
        $this->baseMatriz = rtrim(config('services.matriz_api.base_url'), '/');
        $this->loginUrl = "{$this->baseMatriz}/auth/login";
        $this->profileUrl = "{$this->baseMatriz}/api/v2/profile";
    }

    public function getBaseUrl(): string
    {
        return $this->baseMatriz;
    }

    public function getCookieJar(): CookieJar
    {
        if (Cache::has($this->cacheKey)) {
            Log::info('[Auth] Using cached session data.');
            return $this->createCookieJarFromCache();
        }

        Log::info('[Auth] New Matriz Session');
        $jar = new CookieJar;

        $this->seedAnonymousCookie($jar);

        $initialProfileData = $this->fetchInitialProfileData($jar);

        $this->performLogin($jar, $initialProfileData['csrfToken']);

        $finalProfileData = $this->fetchFinalProfileData($jar);

        $this->cacheSessionData($jar, $finalProfileData);

        return $jar;
    }

    private function createCookieJarFromCache(): CookieJar
    {
        $domain = parse_url($this->baseMatriz, PHP_URL_HOST);
        $cachedData = Cache::get($this->cacheKey);

        return CookieJar::fromArray([
            '_mtz_web_key' => $cachedData['_mtz_web_key'],
        ], $domain);
    }

    private function seedAnonymousCookie(CookieJar $jar): void
    {
        Http::withOptions(['cookies' => $jar])
            ->withHeaders(['Accept' => 'text/html'])
            ->get($this->baseMatriz . '/');

        Log::info('[Auth] Seeded anonymous cookie.');
    }

    private function fetchInitialProfileData(CookieJar $jar): array
    {
        $ds = round(microtime(true) * 1000) . '-' . rand(100000, 999999);
        $response = Http::withOptions(['cookies' => $jar])
            ->withHeaders($this->getDefaultHeaders())
            ->get("{$this->profileUrl}?_ds={$ds}");

        if (! $response->successful()) {
            throw new \Exception("[Auth] Error profile (inicial): {$response->status()}");
        }

        $body = $response->json();
        if (empty($body['csrfToken'])) {
            throw new \Exception('[Auth] Missing csrfToken in initial profile.');
        }

        return [
            'csrfToken' => $body['csrfToken'],
        ];
    }

    private function performLogin(CookieJar $jar, string $csrfToken): void
    {
        $response = Http::withOptions(['cookies' => $jar])
            ->withHeaders(array_merge($this->getDefaultHeaders(), [
                'Content-Type' => 'application/json',
                'X-CSRF-Token' => $csrfToken,
            ]))
            ->post($this->loginUrl, [
                'username' => config('services.matriz_api.username'),
                'password' => config('services.matriz_api.password'),
            ]);

        if (! $response->successful()) {
            throw new \Exception("[Auth] Login failed: {$response->status()}");
        }

        Log::info('[Auth] Login successful.');
    }

    private function fetchFinalProfileData(CookieJar $jar): array
    {
        $ds = round(microtime(true) * 1000) . '-' . rand(100000, 999999);
        $response = Http::withOptions(['cookies' => $jar])
            ->withHeaders($this->getDefaultHeaders())
            ->get("{$this->profileUrl}?_ds={$ds}");

        if (! $response->successful()) {
            throw new \Exception("[Auth] Error profile (final): {$response->status()}");
        }

        $body = $response->json();
        if (empty($body['csrfToken']) || empty($body['connectionId']) || empty($body['sessionId'])) {
            throw new \Exception('[Auth] Missing critical data in final profile.');
        }

        return [
            'csrfToken' => $body['csrfToken'],
            'connectionId' => $body['connectionId'],
            'sessionId' => $body['sessionId'],
        ];
    }

    private function cacheSessionData(CookieJar $jar, array $profileData): void
    {
        $cookieMap = [];
        foreach ($jar->toArray() as $cookie) {
            $cookieMap[$cookie['Name']] = $cookie['Value'];
        }

        $sessionData = array_merge($cookieMap, [
            'connectionId' => $profileData['connectionId'],
            'sessionId' => $profileData['sessionId'],
        ]);

        Cache::put($this->cacheKey, $sessionData, now()->addMinutes(50));
        Log::info('[Auth] Session data cached.');
    }

    private function getDefaultHeaders(): array
    {
        return [
            'accept' => 'application/json, text/plain, */*',
            'accept-language' => 'es-US,es-419;q=0.9,es;q=0.8,it;q=0.7',
            'cache-control' => 'no-cache',
            'dnt' => '1',
            'priority' => 'u=1, i',
            'referer' => $this->baseMatriz . '/',
            'sec-ch-ua' => '"Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
        ];
    }
}