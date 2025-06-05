<?php

namespace Database\Seeders\Cashflows;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Ndt25CashflowSeeder extends Seeder
{
    public function run()
    {
        DB::table('cashflows')->insert([
            [
                'bond_name' => 'NDT25',
                'date' => '2024-10-28',
                'interest' => 3.10,
                'amortization' => 7.69,
                'total' => 10.79,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2025-04-28',
                'interest' => 2.91,
                'amortization' => 7.69,
                'total' => 10.60,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2025-10-27',
                'interest' => 2.63,
                'amortization' => 7.69,
                'total' => 10.32,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2026-04-27',
                'interest' => 2.38,
                'amortization' => 7.69,
                'total' => 10.07,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2026-10-27',
                'interest' => 2.12,
                'amortization' => 7.69,
                'total' => 9.81,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2027-04-27',
                'interest' => 1.85,
                'amortization' => 7.69,
                'total' => 9.54,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2027-10-27',
                'interest' => 1.59,
                'amortization' => 7.69,
                'total' => 9.28,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2028-04-27',
                'interest' => 1.32,
                'amortization' => 7.69,
                'total' => 9.01,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2028-10-27',
                'interest' => 1.06,
                'amortization' => 7.69,
                'total' => 8.75,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2029-04-27',
                'interest' => 0.79,
                'amortization' => 7.69,
                'total' => 8.48,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2029-10-29',
                'interest' => 0.54,
                'amortization' => 7.69,
                'total' => 8.23,
            ],
            [
                'bond_name' => 'NDT25',
                'date' => '2030-04-29',
                'interest' => 0.27,
                'amortization' => 7.72,
                'total' => 7.99,
            ]
        ]);
    }
}