<?php

namespace App\Http\Controllers;

use App\DTOs\CompanyDataDTO;
use App\Repositories\FinancialStatementsRepository;
use App\Repositories\RsiRepository;
use App\Repositories\MacdRepository;
use App\Repositories\CompanyDataRepository;
use App\Services\StockServices\Prices\StockDataApiService;
use App\Services\StockServices\Prices\StockAnalysisApiService;
use App\Services\StockDataTransformerService;
use App\Services\StockServices\CompanyInfo\PolygonStockInfoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class StockController extends Controller
{
    public function __construct(
        private StockDataApiService $stockDataService,
        private StockAnalysisApiService $stockAnalysisService,

        private RsiRepository $rsiRepository,
        private MacdRepository $macdRepository,
        private StockDataTransformerService $stockDataTransformer,
        private FinancialStatementsRepository $financialStatementsRepository,
        private CompanyDataRepository $companyDataRepository,
        private PolygonStockInfoService $polygonStockInfoService,
    ){}

    public function index(Request $request)
    {
        $symbol = $request->input('symbol', 'AAPL');

        if(config('stockdata.db_api_source') === 'API'){
            $companyData = $this->polygonStockInfoService->getCompanyBasicData($symbol);
            if (!empty($companyData['results']['branding']['logo_url'])) {
                $companyData['results']['branding']['logo_url'] .= '?apiKey=' . config('services.polygon_api.token');
            }
            $companyData = CompanyDataDTO::fromApi($apiData['results'] ?? [])->toArray();
            if (config('stockdata.api_source') === 'stockdata') {
                $data = $this->stockDataService->fetchStockPricesFromStockDataApi($symbol);
            } else {
                $data = $this->stockAnalysisService->fetchStockPricesFromStockAnalysisApi($symbol);
            }
        } elseif(config('stockdata.db_api_source') === 'DB'){
            $companyData = $this->companyDataRepository->getCompanyDataFromDb($symbol);
            $companyData = CompanyDataDTO::fromDb($companyData)->toArray();
            $rsiData = $this->rsiRepository->getRsiData($symbol);
            $macdData = $this->macdRepository->getMacdData($symbol);
            $financialData = $this->financialStatementsRepository->getFinancialStatementsFromDb($symbol);
            $data = $this->stockAnalysisService->fetchStockPricesFromDb($symbol);
        }
        $stockData = $this->stockDataTransformer->fromStockAnalysisToFront($data);

        $lastTwoCloses = $this->getLastTwoCloses($stockData);

        return Inertia::render('Stocks/Index', [
            'symbol' => $symbol,
            'stockData' => $stockData,
            'companyData' => $companyData,
            'rsiData' => $rsiData,
            'macdData' => $macdData,
            'financialData' => $financialData,
            'lastTwoCloses' => $lastTwoCloses,
        ], Response::HTTP_OK);
    }

    public function show(string $symbol): JsonResponse
    {
        try {
            $companyData = $this->companyDataRepository->getCompanyDataFromDb($symbol);
            $rsiData = $this->rsiRepository->getRsiData($symbol);
            $macdData = $this->macdRepository->getMacdData($symbol);
            $financialData = $this->financialStatementsRepository->getFinancialStatementsFromDb($symbol);
            $data = $this->stockAnalysisService->fetchStockPricesFromDb($symbol);
            $stockData = $this->stockDataTransformer->fromStockAnalysisToFront($data);
            $lastTwoCloses = $this->getLastTwoCloses($stockData);

            return response()->json([
                'symbol' => $symbol,
                'stockData' => $stockData,
                'companyData' => $companyData,
                'rsiData' => $rsiData,
                'macdData' => $macdData,
                'financialData' => $financialData,
                'lastTwoCloses' => $lastTwoCloses,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Stock data could not be retrieved.'], 404);
        }
    }

    private function getLastTwoCloses(array $stockData): array
    {
        if (empty($stockData['data']) || count($stockData['data']) < 2) {
            return [];
        }
        $sorted = $stockData['data'];
        usort($sorted, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));
        $last = end($sorted);
        $prev = prev($sorted);
        return [
            'lastClose' => $last['close'] ?? null,
            'prevClose' => $prev['close'] ?? null,
        ];
    }
}
