<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'team_id',
        'product_id',
        'user_id',
        'quantity',
        'type', // addition, reduction, damaged, discrepancy, transfer
        'reason',
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
        ];
    }

    /**
     * Get the branch where adjustment occurred.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Get the product that was adjusted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who executed the adjustment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
