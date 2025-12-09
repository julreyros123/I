<?php

namespace Database\Seeders;

use App\Models\BillingRecord;
use App\Models\Customer;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

class BillingRecordSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('en_PH');
        $preparedBy = 'System Billing Seeder';
        $purokNames = [
            'Purok 1 - Sto. Niño',
            'Purok 2 - Shanghai',
            'Purok 3 - Maligaya',
            'Purok 4 - New Bohol',
            'Purok 5 - Kawayan',
            'Purok 6 - Talisay',
        ];

        $customers = Customer::orderBy('id')->get();

        if ($customers->isEmpty()) {
            for ($i = 1; $i <= 200; $i++) {
                $accountNo = sprintf('22-%06d', $i);
                $purok = $purokNames[($i - 1) % count($purokNames)];

                $customer = Customer::updateOrCreate(
                    ['account_no' => $accountNo],
                    [
                        'name' => $faker->name(),
                        'address' => $this->composeAddressWithPurok(null, $purok, $faker),
                        'contact_no' => $faker->numerify('09#########'),
                        'meter_no' => sprintf('MTR-%05d', $i),
                        'meter_size' => $faker->randomElement(['15mm', '20mm', '25mm']),
                        'status' => 'Active',
                        'previous_reading' => 0,
                    ]
                );

                $this->seedBillingRecordForCustomer($customer, $preparedBy, $faker, $i);
            }

            return;
        }

        foreach ($customers as $index => $customer) {
            $purok = $purokNames[$index % count($purokNames)];

            $customer->forceFill([
                'status' => 'Active',
                'address' => $this->composeAddressWithPurok($customer->address, $purok, $faker),
                'contact_no' => $customer->contact_no ?: $faker->numerify('09#########'),
                'meter_no' => $customer->meter_no ?: sprintf('MTR-%05d', $index + 1),
                'meter_size' => $customer->meter_size ?: $faker->randomElement(['15mm', '20mm', '25mm']),
            ])->save();

            $this->seedBillingRecordForCustomer($customer, $preparedBy, $faker, $index + 1);
        }
    }

    private function composeAddressWithPurok(?string $address, string $purok, $faker): string
    {
        $components = array_filter(array_map('trim', explode(',', (string) $address)));
        $street = $components[0] ?? $faker->streetAddress();

        return sprintf('%s, %s, Manambulan', $street, $purok);
    }

    private function seedBillingRecordForCustomer(Customer $customer, string $preparedBy, $faker, int $sequence): void
    {
        $referenceMonth = Carbon::now()->subMonths(rand(0, 8));
        $readingDate = (clone $referenceMonth)->startOfMonth()->setDay(5);
        $coverageStart = (clone $readingDate)->subMonth()->setDay(6);
        $dueDate = (clone $referenceMonth)->startOfMonth()->setDay(25);
        $issuedAt = (clone $readingDate)->addDay()->setTime(rand(8, 16), rand(0, 59));

        $previousReading = $customer->previous_reading ?? $faker->numberBetween(0, 150);
        $consumption = $faker->numberBetween(15, 45);
        $currentReading = $previousReading + $consumption;

        $baseRate = $faker->randomFloat(2, 28, 38);
        $maintenanceCharge = $faker->randomFloat(2, 30, 80);
        $overduePenalty = $faker->boolean(20) ? $faker->randomFloat(2, 20, 60) : 0.0;
        $advancePayment = 0.0;
        $vat = round(($consumption * $baseRate) * 0.12, 2);

        $consumptionCost = round($consumption * $baseRate, 2);
        $totalAmount = max(0, $consumptionCost + $maintenanceCharge + $overduePenalty + $vat - $advancePayment);

        $invoiceNumber = sprintf(
            'INV-%s-%s-%03d',
            $readingDate->format('Ym'),
            preg_replace('/[^0-9A-Za-z]/', '', $customer->account_no),
            $sequence
        );

        BillingRecord::updateOrCreate(
            ['invoice_number' => $invoiceNumber],
            [
                'customer_id' => $customer->id,
                'account_no' => $customer->account_no,
                'previous_reading' => $previousReading,
                'current_reading' => $currentReading,
                'consumption_cu_m' => $consumption,
                'base_rate' => $baseRate,
                'maintenance_charge' => $maintenanceCharge,
                'advance_payment' => $advancePayment,
                'overdue_penalty' => $overduePenalty,
                'vat' => $vat,
                'total_amount' => $totalAmount,
                'bill_status' => 'Outstanding Payment',
                'is_generated' => false,
                'generated_at' => null,
                'notes' => 'Seeded record for print testing',
                'date_from' => $coverageStart,
                'date_to' => $readingDate,
                'due_date' => $dueDate,
                'prepared_by' => $preparedBy,
                'issued_at' => $issuedAt,
            ]
        );

        $customer->update(['previous_reading' => $currentReading]);
    }
}
