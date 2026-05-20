<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'team_id',
        'tenant_id',
        'items',
        'subtotal',
        'total_vat',
        'total',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
