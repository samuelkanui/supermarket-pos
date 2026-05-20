<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    /**
     * Bootstrap the model and its events.
     */
    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            \App\Support\BarcodeCache::set($product);
        });

        static::deleted(function (Product $product) {
            \App\Support\BarcodeCache::forget($product->tenant_id, $product->barcode);
        });
    }

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'cost_price',
        'selling_price',
        'vat_rate',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the branch stock records for this product.
     */
    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    /**
     * Get the stock adjustments for this product.
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Get the stock quantity for a specific branch (team).
     */
    public function stockForBranch(int|Team $branch): float|int
    {
        $branchId = $branch instanceof Team ? $branch->id : $branch;
        
        $stock = $this->branchStocks()
            ->where('team_id', $branchId)
            ->first();

        return $stock ? (float) $stock->quantity : 0.0;
    }
}
