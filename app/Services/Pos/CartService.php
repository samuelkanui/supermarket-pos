<?php

namespace App\Services\Pos;

use App\Models\BranchStock;
use App\Models\Product;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService
{
    /**
     * Validate stock levels for each cart item and update branch stock quantities.
     *
     * @param Team $branch The active branch (team) where the sale occurs.
     * @param array $items  Array of cart items [{product_id, quantity, price, vat_rate}] .
     * @throws \Exception if stock is insufficient.
     */
    public function processCart(Team $branch, array $items): void
    {
        DB::transaction(function () use ($branch, $items) {
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                // Lock the stock row for update to prevent race conditions.
                $stock = BranchStock::where('team_id', $branch->id)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    // If no stock record exists, treat as zero quantity.
                    throw new ModelNotFoundException('Stock record not found for product ID ' . $productId);
                }

                if ((float) $stock->quantity < (float) $quantity) {
                    throw new \Exception('Insufficient stock for product ID ' . $productId);
                }

                // Decrement stock.
                $stock->decrement('quantity', $quantity);
            }
        });
    }
}
