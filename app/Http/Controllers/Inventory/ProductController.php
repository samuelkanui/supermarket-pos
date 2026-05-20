<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $products = Product::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return Inertia::render('inventory/Products', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        // Enforce SKU unique within tenant
        $exists = Product::where('sku', $validated['sku'])->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['sku' => 'SKU already exists in your catalog.']);
        }

        Product::create($validated);

        return redirect()->back()->with('message', 'Product created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        // Enforce SKU unique within tenant except itself
        $exists = Product::where('sku', $validated['sku'])->where('id', '!=', $product->id)->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['sku' => 'SKU already exists in your catalog.']);
        }

        $product->update($validated);

        return redirect()->back()->with('message', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('message', 'Product deleted successfully.');
    }
}
