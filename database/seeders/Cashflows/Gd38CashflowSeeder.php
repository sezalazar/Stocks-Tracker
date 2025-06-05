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
                'bond_name' => 'GD38',
                'date' => '2024-07-10',
                'interest' => 2.14,
                'amortization' => 0.00,
                'total' => 2.14,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2025-01-09',
                'interest' => 2.49,
                'amortization' => 0.00,
                'total' => 2.49,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2025-07-10',
                'interest' => 2.51,
                'amortization' => 0.00,
                'total' => 2.51,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2026-01-09',
                'interest' => 2.49,
                'amortization' => 0.00,
                'total' => 2.49,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2026-07-10',
                'interest' => 2.51,
                'amortization' => 0.00,
                'total' => 2.51,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2027-01-11',
                'interest' => 2.51,
                'amortization' => 0.00,
                'total' => 2.51,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2027-07-12',
                'interest' => 2.51,
                'amortization' => 4.54,
                'total' => 7.05,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2028-01-10',
                'interest' => 2.36,
                'amortization' => 4.54,
                'total' => 6.90,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2028-07-10',
                'interest' => 2.27,
                'amortization' => 4.54,
                'total' => 6.81,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2029-01-09',
                'interest' => 2.15,
                'amortization' => 4.54,
                'total' => 6.69,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2029-07-10',
                'interest' => 2.06,
                'amortization' => 4.54,
                'total' => 6.60,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2030-01-09',
                'interest' => 1.92,
                'amortization' => 4.54,
                'total' => 6.46,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2030-07-10',
                'interest' => 1.83,
                'amortization' => 4.54,
                'total' => 6.37,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2031-01-09',
                'interest' => 1.70,
                'amortization' => 4.54,
                'total' => 6.24,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2031-07-10',
                'interest' => 1.60,
                'amortization' => 4.54,
                'total' => 6.14,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2032-01-09',
                'interest' => 1.47,
                'amortization' => 4.54,
                'total' => 6.01,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2032-07-12',
                'interest' => 1.39,
                'amortization' => 4.54,
                'total' => 5.93,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2033-01-10',
                'interest' => 1.24,
                'amortization' => 4.54,
                'total' => 5.78,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2033-07-11',
                'interest' => 1.14,
                'amortization' => 4.54,
                'total' => 5.68,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2034-01-09',
                'interest' => 1.01,
                'amortization' => 4.54,
                'total' => 5.55,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2034-07-10',
                'interest' => 0.92,
                'amortization' => 4.54,
                'total' => 5.46,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2035-01-09',
                'interest' => 0.79,
                'amortization' => 4.54,
                'total' => 5.33,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2035-07-10',
                'interest' => 0.69,
                'amortization' => 4.54,
                'total' => 5.23,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2036-01-09',
                'interest' => 0.57,
                'amortization' => 4.54,
                'total' => 5.11,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2036-07-10',
                'interest' => 0.46,
                'amortization' => 4.54,
                'total' => 5.00,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2037-01-09',
                'interest' => 0.34,
                'amortization' => 4.54,
                'total' => 4.88,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2037-07-10',
                'interest' => 0.23,
                'amortization' => 4.54,
                'total' => 4.77,
            ],
            [
                'bond_name' => 'GD38',
                'date' => '2038-01-11',
                'interest' => 0.12,
                'amortization' => 4.66,
                'total' => 4.78,
            ]
        ]);
    }
}