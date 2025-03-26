<?php

namespace App\Services;

use App\DTOs\StockDataDTO;

class StockDataTransformerService
{
    public function fromStockAnalysisToFront(array $data): array
    {
        $dataDTOs = array_map(fn($item) => StockDataDTO::fromStockAnalysis($item), $data);
        return $this->dtosToArray($dataDTOs);
    }

    public function fromStockDataOrgToFront(array $response): array
    {
        $dataDTOs = array_map(fn($item) => StockDataDTO::fromStockDataOrg($item), $response['data'] ?? []);
        return $this->dtosToArray($dataDTOs);
    }

    public function fromDatabaseToFront(array $data): array
    {
        $dataDTOs = array_map(fn($item) => StockDataDTO::fromDatabase($item), $data);
        return $this->dtosToArray($dataDTOs);
    }

    private function dtosToArray(array $dataDTOs): array
    {
        return [
            'data' => array_map(fn($dto) => [
                'date' => $dto->date,
                'open' => $dto->open,
                'high' => $dto->high,
                'low' => $dto->low,
                'close' => $dto->close,
                'volume' => $dto->volume,
            ], $dataDTOs)
        ];
    }
}
