<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = [
        'product_id', 'warehouse_id', 'total_quantity', 'reserved_quantity',
        'reorder_level', 'company_id'
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_level' => 'integer',
        'available_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Material\Material::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_quantity <= $this->reorder_level;
    }
}
