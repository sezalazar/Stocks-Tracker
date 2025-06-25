<?php

namespace App\Http\Middleware;

use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'dashboardLists' => fn () => app(DashboardDataService::class)->getDashboardLists(),
            'marketData' => fn () => [
                'fearAndGreed' => app(\App\Services\MarketServices\FearGreedIndexService::class)->fetch(),
            ],
        ];
    }
}