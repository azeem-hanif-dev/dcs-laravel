<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Material\Material;

class SalesReturnItem extends Model
{
    protected $fillable = ['sales_return_id', 'product_id', 'quantity', 'unit_price', 'total', 'reason'];

    protected $casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'total' => 'decimal:2'];

    public function salesReturn(): BelongsTo { return $this->belongsTo(SalesReturn::class); }
    public function product(): BelongsTo { return $this->belongsTo(Material::class, 'product_id'); }
}
