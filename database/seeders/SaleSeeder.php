<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    private const SALES_COUNT = 40;

    public function run(): void
    {
        $products = Product::query()->where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->call([ProductSeeder::class]);
            $products = Product::query()->where('is_active', true)->get();
        }

        if ($products->isEmpty()) {
            $this->command?->warn('No hay productos activos para generar ventas.');
            return;
        }

        $firstNames = [
            'Juan',
            'María',
            'Pedro',
            'Ana',
            'Luis',
            'Carla',
            'José',
            'Lucía',
            'Miguel',
            'Sofía',
            'Diego',
            'Valentina',
        ];

        $lastNames = [
            'Gómez',
            'Pérez',
            'Rodríguez',
            'García',
            'Fernández',
            'López',
            'Martínez',
            'Sánchez',
            'Díaz',
            'Romero',
        ];

        $deliveryLocations = [
            'Depósito Central',
            'Sucursal Centro',
            'Sucursal Norte',
            'Sucursal Sur',
            'Casa del cliente',
            'Punto de entrega',
            'Local',
        ];

        for ($i = 0; $i < self::SALES_COUNT; $i++) {
            DB::transaction(function () use ($products, $firstNames, $lastNames, $deliveryLocations) {
                $createdAt = now()
                    ->subDays(random_int(0, 90))
                    ->subMinutes(random_int(0, 60 * 24));

                $itemsCount = min(random_int(1, 4), $products->count());
                $selectedProducts = $products->random($itemsCount);

                $totalCents = 0;
                $itemRows = [];

                foreach ($selectedProducts as $product) {
                    $quantity = random_int(1, 12);
                    $unitPriceCents = random_int(200, 50000); // 2.00 a 500.00
                    $lineTotalCents = $quantity * $unitPriceCents;

                    $totalCents += $lineTotalCents;

                    $itemRows[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $this->centsToMoney($unitPriceCents),
                        'line_total' => $this->centsToMoney($lineTotalCents),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }

                $customerName = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];

                $deliveryLocation = random_int(1, 100) <= 60
                    ? $deliveryLocations[array_rand($deliveryLocations)]
                    : null;

                $scenarioRoll = random_int(1, 100);
                $totalPaidCents = 0;
                $dueDate = null;

                if ($scenarioRoll <= 35) {
                    // Totalmente pagada.
                    $totalPaidCents = $totalCents;
                    $dueDate = null;
                } elseif ($scenarioRoll <= 85) {
                    // Pagos parciales.
                    $maxPaid = max($totalCents - 100, 0);
                    $totalPaidCents = $maxPaid > 0 ? random_int(0, $maxPaid) : 0;
                    $dueDate = Carbon::parse($createdAt)->addDays(random_int(5, 60))->toDateString();
                } else {
                    // Sin pagos todavía.
                    $totalPaidCents = 0;
                    $dueDate = Carbon::parse($createdAt)->addDays(random_int(5, 60))->toDateString();
                }

                $sale = Sale::create([
                    'customer_name' => $customerName,
                    'delivery_location' => $deliveryLocation,
                    'due_date' => $totalPaidCents < $totalCents ? $dueDate : null,
                    'total_amount' => $this->centsToMoney($totalCents),
                ]);

                $sale->forceFill([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ])->save();

                $itemRows = array_map(function (array $row) use ($sale) {
                    $row['sale_id'] = $sale->id;
                    return $row;
                }, $itemRows);

                DB::table('sale_items')->insert($itemRows);

                $paymentRows = $this->buildPaymentRows($sale->id, $totalPaidCents, $createdAt);
                if (count($paymentRows) > 0) {
                    DB::table('sale_payments')->insert($paymentRows);

                    $lastPaidAt = end($paymentRows)['paid_at'] ?? null;
                    if ($lastPaidAt) {
                        $sale->forceFill(['updated_at' => $lastPaidAt])->save();
                    }
                }
            });
        }
    }

    private function buildPaymentRows(int $saleId, int $totalPaidCents, Carbon $saleCreatedAt): array
    {
        if ($totalPaidCents <= 0) {
            return [];
        }

        $maxPayments = min(3, $totalPaidCents);
        $paymentsCount = random_int(1, $maxPayments);

        $remaining = $totalPaidCents;
        $rows = [];

        for ($i = 0; $i < $paymentsCount; $i++) {
            if ($i === $paymentsCount - 1) {
                $paymentCents = $remaining;
            } else {
                $max = $remaining - ($paymentsCount - $i - 1);
                $paymentCents = random_int(1, $max);
            }

            $remaining -= $paymentCents;

            $paidAt = $saleCreatedAt
                ->copy()
                ->addDays(random_int(0, 20))
                ->addMinutes(random_int(0, 60 * 12));

            if ($paidAt->greaterThan(now())) {
                $paidAt = now();
            }

            $rows[] = [
                'sale_id' => $saleId,
                'amount' => $this->centsToMoney($paymentCents),
                'paid_at' => $paidAt,
                'created_at' => $paidAt,
                'updated_at' => $paidAt,
            ];
        }

        usort($rows, fn (array $a, array $b) => ($a['paid_at'] <=> $b['paid_at']));

        return $rows;
    }

    private function centsToMoney(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}