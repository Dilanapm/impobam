<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\StockOutput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_require_authentication(): void
    {
        $this->get(route('admin.reports.sales', absolute: false))
            ->assertRedirect('/login');

        $this->get(route('admin.reports.outputs', absolute: false))
            ->assertRedirect('/login');

        $this->get(route('admin.reports.pending-payments', absolute: false))
            ->assertRedirect('/login');
    }

    public function test_reports_return_pdf_when_authenticated(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Producto prueba',
            'is_active' => true,
        ]);

        StockOutput::create([
            'product_id' => $product->id,
            'quantity' => 3,
            'employee_name' => 'Empleado',
            'notes' => 'Salida de prueba',
            'moved_at' => now(),
        ]);

        $sale = Sale::create([
            'customer_name' => 'Cliente',
            'delivery_location' => 'Depósito',
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => '100.00',
        ]);

        SalePayment::create([
            'sale_id' => $sale->id,
            'amount' => '20.00',
            'paid_at' => now(),
        ]);

        $salesPdf = $this->actingAs($user)->get(route('admin.reports.sales', absolute: false));
        $salesPdf->assertOk();
        $this->assertStringStartsWith('%PDF', $salesPdf->getContent());

        $outputsPdf = $this->actingAs($user)->get(route('admin.reports.outputs', absolute: false));
        $outputsPdf->assertOk();
        $this->assertStringStartsWith('%PDF', $outputsPdf->getContent());

        $pendingPdf = $this->actingAs($user)->get(route('admin.reports.pending-payments', absolute: false));
        $pendingPdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pendingPdf->getContent());

        $today = now()->toDateString();

        $salesPdfCustom = $this->actingAs($user)->get(route('admin.reports.sales', ['start_date' => $today, 'end_date' => $today], absolute: false));
        $salesPdfCustom->assertOk();
        $this->assertStringStartsWith('%PDF', $salesPdfCustom->getContent());

        $outputsPdfCustom = $this->actingAs($user)->get(route('admin.reports.outputs', ['start_date' => $today, 'end_date' => $today], absolute: false));
        $outputsPdfCustom->assertOk();
        $this->assertStringStartsWith('%PDF', $outputsPdfCustom->getContent());

        $pendingPdfCustom = $this->actingAs($user)->get(route('admin.reports.pending-payments', ['start_date' => $today, 'end_date' => $today], absolute: false));
        $pendingPdfCustom->assertOk();
        $this->assertStringStartsWith('%PDF', $pendingPdfCustom->getContent());
    }
}
