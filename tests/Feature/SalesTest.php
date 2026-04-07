<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_create_page_renders(): void
    {
        $this->get('/ventas')->assertOk();
    }

    public function test_sale_can_be_created_with_multiple_products_and_initial_payment(): void
    {
        $productA = Product::create(['name' => 'Producto A', 'is_active' => true]);
        $productB = Product::create(['name' => 'Producto B', 'is_active' => true]);

        $response = $this->post('/ventas', [
            'customer_name' => 'Cliente 1',
            'delivery_location' => 'Depósito',
            'due_date' => now()->addDays(7)->toDateString(),
            'initial_payment_amount' => '10.00',
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 2, 'unit_price' => '5.00'],
                ['product_id' => $productB->id, 'quantity' => 1, 'unit_price' => '10.00'],
            ],
        ]);

        $sale = Sale::query()->firstOrFail();

        $response->assertRedirect(route('sales.show', $sale));

        $this->assertSame('20.00', $sale->total_amount);
        $this->assertCount(2, $sale->items);
        $this->assertCount(1, $sale->payments);
        $this->assertSame('10.00', $sale->payments->first()->amount);
    }

    public function test_due_date_is_required_when_partial_payment_is_provided(): void
    {
        $productA = Product::create(['name' => 'Producto A', 'is_active' => true]);

        $response = $this->from('/ventas')->post('/ventas', [
            'customer_name' => 'Cliente 1',
            'initial_payment_amount' => '1.00',
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 1, 'unit_price' => '5.00'],
            ],
        ]);

        $response->assertRedirect('/ventas');
        $response->assertSessionHasErrors(['due_date']);
    }

    public function test_due_date_is_optional_when_no_payment_is_provided(): void
    {
        $productA = Product::create(['name' => 'Producto A', 'is_active' => true]);

        $response = $this->post('/ventas', [
            'customer_name' => 'Cliente 1',
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 1, 'unit_price' => '5.00'],
            ],
        ]);

        $sale = Sale::query()->firstOrFail();

        $response->assertRedirect(route('sales.show', $sale));
        $this->assertNull($sale->due_date);
    }

    public function test_due_date_cannot_be_in_the_past(): void
    {
        $productA = Product::create(['name' => 'Producto A', 'is_active' => true]);

        $response = $this->from('/ventas')->post('/ventas', [
            'customer_name' => 'Cliente 1',
            'due_date' => now()->subDay()->toDateString(),
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 1, 'unit_price' => '10.00'],
            ],
        ]);

        $response->assertRedirect('/ventas');
        $response->assertSessionHasErrors(['due_date']);
        $this->assertSame(0, Sale::query()->count());
    }

    public function test_payment_cannot_exceed_balance(): void
    {
        $productA = Product::create(['name' => 'Producto A', 'is_active' => true]);

        $saleResponse = $this->post('/ventas', [
            'customer_name' => 'Cliente 1',
            'due_date' => now()->addDays(7)->toDateString(),
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 1, 'unit_price' => '10.00'],
            ],
        ]);

        $sale = Sale::query()->firstOrFail();
        $saleResponse->assertRedirect(route('sales.show', $sale));

        $response = $this->from(route('sales.show', $sale))->post(route('sales.payments.store', $sale), [
            'amount' => '20.00',
            'next_due_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertRedirect(route('sales.show', $sale));
        $response->assertSessionHasErrors(['amount']);
        $this->assertCount(0, $sale->fresh()->payments);
    }

    public function test_next_due_date_cannot_be_in_the_past_when_registering_payment(): void
    {
        $productA = Product::create(['name' => 'Producto A', 'is_active' => true]);

        $saleResponse = $this->post('/ventas', [
            'customer_name' => 'Cliente 1',
            'due_date' => now()->addDays(7)->toDateString(),
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 1, 'unit_price' => '10.00'],
            ],
        ]);

        $sale = Sale::query()->firstOrFail();
        $saleResponse->assertRedirect(route('sales.show', $sale));

        $response = $this->from(route('sales.show', $sale))->post(route('sales.payments.store', $sale), [
            'amount' => '5.00',
            'next_due_date' => now()->subDay()->toDateString(),
        ]);

        $response->assertRedirect(route('sales.show', $sale));
        $response->assertSessionHasErrors(['next_due_date']);
        $this->assertCount(0, $sale->fresh()->payments);
    }
}
