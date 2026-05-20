<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
    ];
}
