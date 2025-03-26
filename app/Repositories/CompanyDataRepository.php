<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CompanyDataRepository
{
    public function getCompanyDataFromDb(string $symbol): array
    {
        $data = DB::table('company_data')
            ->where('ticker', $symbol)
            ->first();

        return $data ? (array) $data : [];
    }
}
