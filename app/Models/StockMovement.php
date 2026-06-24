<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Material\Material;

class StockMovement extends Model
{
    protected $fillable = [
        'stock_id', 'product_id', 'warehouse_id', 'company_id', 'user_id',
        'type', 'quantity_change', 'quantity_before', 'quantity_after',
        'reference_type', 'reference_id', 'reference_number', 'notes',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    // ─── Relationships ──────────────────────────────
    public function stock(): BelongsTo { return $this->belongsTo(Stock::class); }
    public function product(): BelongsTo { return $this->belongsTo(Material::class, 'product_id'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }

    // ─── Type Labels ────────────────────────────────
    public static function typeLabels(): array
    {
        return [
            'purchase_received'  => 'Purchase Received',
            'sales_shipped'      => 'Sales Shipped',
            'sales_returned'     => 'Sales Return',
            'purchase_returned'  => 'Purchase Return',
            'manual_addition'    => 'Manual Addition',
            'manual_removal'     => 'Manual Removal',
            'reserved'           => 'Reserved',
            'released'           => 'Released',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function getIsInAttribute(): bool { return $this->quantity_change > 0; }
    public function getIsOutAttribute(): bool { return $this->quantity_change < 0; }
}
