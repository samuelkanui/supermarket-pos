<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    /**
     * Resolve the current branch (team).
     */
    protected function getBranch(Request $request): Team
    {
        $branch = $request->route('current_team');
        
        if (is_string($branch)) {
            $branch = Team::where('slug', $branch)->firstOrFail();
        }

        return $branch;
    }

    /**
     * Display current branch stock levels.
     */
    public function index(Request $request): Response
    {
        $branch = $this->getBranch($request);

        $products = Product::where('is_active', true)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Attach quantity and threshold to each product for the active branch
        $products->getCollection()->transform(function ($product) use ($branch) {
            $stock = BranchStock::where('team_id', $branch->id)
                ->where('product_id', $product->id)
                ->first();

            $product->stock_quantity = $stock ? (float) $stock->quantity : 0.0;
            $product->low_stock_threshold = $stock ? (float) $stock->low_stock_threshold : 10.0;
            $product->is_low_stock = $product->stock_quantity <= $product->low_stock_threshold;
            
            return $product;
        });

        // Get recent adjustments for auditing
        $adjustments = StockAdjustment::with(['product', 'user'])
            ->where('team_id', $branch->id)
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('inventory/Stocks', [
            'products' => $products,
            'adjustments' => $adjustments,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Execute a stock adjustment for the active branch.
     */
    public function adjust(Request $request)
    {
        $branch = $this->getBranch($request);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric', // Positive for additions, negative for reductions
            'type' => 'required|string|in:addition,reduction,damaged,discrepancy,transfer',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($branch, $request, $validated) {
            // Log adjustment audit trail
            StockAdjustment::create([
                'team_id' => $branch->id,
                'product_id' => $validated['product_id'],
                'user_id' => $request->user()->id,
                'quantity' => $validated['quantity'],
                'type' => $validated['type'],
                'reason' => $validated['reason'],
            ]);

            // Update or create BranchStock count
            $stock = BranchStock::firstOrCreate([
                'team_id' => $branch->id,
                'product_id' => $validated['product_id'],
            ], [
                'tenant_id' => $branch->tenant_id,
                'quantity' => 0.0,
                'low_stock_threshold' => 10.0,
            ]);

            $stock->increment('quantity', $validated['quantity']);
        });

        return redirect()->back()->with('message', 'Stock adjustment applied successfully.');
    }
}
