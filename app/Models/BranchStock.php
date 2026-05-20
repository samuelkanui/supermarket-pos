<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchStock extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'team_id',
        'product_id',
        'quantity',
        'low_stock_threshold',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'low_stock_threshold' => 'decimal:2',
        ];
    }

    /**
     * Get the branch (team) that owns this stock record.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Get the product associated with this stock record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
