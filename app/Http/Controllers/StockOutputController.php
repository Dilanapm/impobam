<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOutput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockOutputController extends Controller
{
    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->whereRaw('LOWER(name) != ?', ['ballerina acondicionador'])
            ->orderBy('name')
            ->get();

        $recentOutputs = StockOutput::with('product')
            ->latest('moved_at')
            ->latest('id')
            ->take(5)
            ->get();

        $lastOutputId = session('employee_last_output_id');

        return view('stock-outputs.create', compact(
            'products',
            'recentOutputs',
            'lastOutputId'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'employee_name' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        
        $stockOutput = StockOutput::create([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'employee_name' => $validated['employee_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'moved_at' => now(),
        ]);

        session([
            'employee_last_output_id' => $stockOutput->id,
        ]);

        return redirect()
            ->route('stock-outputs.create')
            ->with('status', 'Salida registrada correctamente. Si te equivocaste, puedes eliminar solo este último registro.');
    }

    public function destroyOwn(Request $request, StockOutput $stockOutput): RedirectResponse
    {
        $lastOutputId = (int) $request->session()->get('employee_last_output_id', 0);

        if ($lastOutputId !== (int) $stockOutput->id) {
            abort(403, 'No puedes eliminar este registro.');
        }

        $stockOutput->delete();

        $request->session()->forget('employee_last_output_id');

        return redirect()
            ->route('stock-outputs.create')
            ->with('status', 'Tu último registro fue eliminado correctamente.');
    }
}