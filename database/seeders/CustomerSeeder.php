<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'account_no' => '000123',
                'name' => 'John Doe',
                'address' => 'Brgy. Montalban',
                'meter_no' => 'M-1001',
                'meter_size' => '20mm',
                'status' => 'Active',
                'previous_reading' => 0,
            ],
            [
                'account_no' => '000124',
                'name' => 'Jane Doe',
                'address' => 'Brgy. San Isidro',
                'meter_no' => 'M-1002',
                'meter_size' => '15mm',
                'status' => 'Active',
                'previous_reading' => 0,
            ],
        ];

        foreach ($rows as $row) {
            Customer::updateOrCreate(['account_no' => $row['account_no']], $row);
        }
    }
}


