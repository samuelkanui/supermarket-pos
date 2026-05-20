<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Show the POS checkout page.
     */
    public function index(): Response
    {
        // Fetch all active products to be used for barcode lookup
        $products = \App\Models\Product::with(['category', 'supplier'])->orderBy('name')->get();
        return Inertia::render('pos/Checkout', ['products' => $products]);
    }
}
