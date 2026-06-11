<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Stock;
use App\Models\SalesOrderItem;

class Material extends Model
{
    protected $fillable = [
        'material_name', 'category_id', 'subcategory_id', 'price',
        'total_quantity', 'assigned_quantity', 'description', 'status',
        'uses', 'supplier_id', 'user_id', 'company_id',
        'sku', 'unit', 'reorder_level'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_quantity' => 'integer',
        'assigned_quantity' => 'integer',
        'reorder_level' => 'integer',
        'uses' => 'integer',
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subcategory(): BelongsTo { return $this->belongsTo(Subcategory::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }

    public function stocks(): HasMany { return $this->hasMany(Stock::class, 'product_id'); }
    public function salesOrderItems(): HasMany { return $this->hasMany(SalesOrderItem::class, 'product_id'); }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, (int)$this->total_quantity - (int)$this->assigned_quantity);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->remaining_quantity <= ($this->reorder_level ?? 10);
    }
}
