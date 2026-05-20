<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\StoreSaleRequest;
use App\Models\Sale;
use App\Models\BranchStock;
use App\Services\Pos\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Store a new sale.
     */
    public function store(StoreSaleRequest $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validated(); // contains cart items, payment_method, payment_reference
        $branch = $request->route('current_team'); // resolved via middleware
        if (is_string($branch)) {
            $branch = \App\Models\Team::where('slug', $branch)->firstOrFail();
        }
        DB::transaction(function () use ($validated, $branch, $cartService) {
            // Validate stock levels and adjust
            $cartService->processCart($branch, $validated['items']);
            // Compute totals
            $subtotal = collect($validated['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
            $totalVat = collect($validated['items'])->sum(fn($i) => ($i['price'] * $i['quantity']) * ($i['vat_rate'] / 100);
            $total = $subtotal + $totalVat;
            // Create sale record
            Sale::create([
                'team_id' => $branch->id,
                'tenant_id' => $branch->tenant_id,
                'items' => json_encode($validated['items']),
                'subtotal' => $subtotal,
                'total_vat' => $totalVat,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
            ]);
        });
        return redirect()->back()->with('message', 'Sale completed successfully.');
    }
}
