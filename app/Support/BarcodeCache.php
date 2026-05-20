<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Redis;

class BarcodeCache
{
    /**
     * Get the Redis cache key for a barcode within a tenant.
     */
    protected static function getKey(int $tenantId, string $barcode): string
    {
        return "tenant:{$tenantId}:barcode:{$barcode}";
    }

    /**
     * Store a product in Redis by barcode.
     */
    public static function set(Product $product): void
    {
        if (empty($product->barcode)) {
            return;
        }

        $key = self::getKey($product->tenant_id, $product->barcode);
        
        // Cache the product representation as JSON, set TTL to 24 hours (86400 seconds)
        Redis::setex($key, 86400, json_encode([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'cost_price' => $product->cost_price,
            'selling_price' => $product->selling_price,
            'vat_rate' => $product->vat_rate,
            'category_id' => $product->category_id,
            'is_active' => $product->is_active,
        ]));
    }

    /**
     * Find a product in Redis by barcode.
     */
    public static function get(int $tenantId, string $barcode): ?array
    {
        $key = self::getKey($tenantId, $barcode);
        $cached = Redis::get($key);

        if ($cached) {
            return json_decode($cached, true);
        }

        return null;
    }

    /**
     * Delete a product from Redis by barcode.
     */
    public static function forget(int $tenantId, ?string $barcode): void
    {
        if (empty($barcode)) {
            return;
        }

        $key = self::getKey($tenantId, $barcode);
        Redis::del($key);
    }
}
