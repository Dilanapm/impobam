<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_note_pdf_can_be_downloaded(): void
    {
        $product = Product::create([
            'name' => 'Producto prueba',
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'customer_name' => 'Cliente',
            'delivery_location' => 'Depósito',
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => '100.00',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '50.00',
            'line_total' => '100.00',
        ]);

        $response = $this->get(route('sales.note', $sale, absolute: false));
        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_payment_receipt_pdf_can_be_downloaded(): void
    {
        $sale = Sale::create([
            'customer_name' => 'Cliente',
            'delivery_location' => null,
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => '100.00',
        ]);

        $payment = SalePayment::create([
            'sale_id' => $sale->id,
            'amount' => '25.00',
            'paid_at' => now(),
        ]);

        $response = $this->get(route('sales.payments.receipt', ['sale' => $sale, 'payment' => $payment], absolute: false));
        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_payment_receipt_returns_404_when_payment_does_not_belong_to_sale(): void
    {
        $saleA = Sale::create([
            'customer_name' => 'Cliente A',
            'delivery_location' => null,
            'due_date' => null,
            'total_amount' => '10.00',
        ]);

        $saleB = Sale::create([
            'customer_name' => 'Cliente B',
            'delivery_location' => null,
            'due_date' => null,
            'total_amount' => '20.00',
        ]);

        $paymentB = SalePayment::create([
            'sale_id' => $saleB->id,
            'amount' => '5.00',
            'paid_at' => now(),
        ]);

        $this->get(route('sales.payments.receipt', ['sale' => $saleA, 'payment' => $paymentB], absolute: false))
            ->assertNotFound();
    }
}
