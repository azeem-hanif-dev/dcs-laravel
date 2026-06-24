<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Material\Material;

class Stock extends Model
{
    protected $fillable = [
        'product_id', 'warehouse_id', 'total_quantity', 'reserved_quantity',
        'reorder_level', 'company_id'
    ];

    protected $casts = [
        'total_quantity'   => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_level'     => 'integer',
    ];

    // hidden from array output — use accessors instead
    protected $appends = ['available_quantity', 'status', 'status_color', 'stock_value'];

    // ─── Relationships ──────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Computed Accessors ─────────────────────────

    /**
     * Units currently available for sale (total minus reserved).
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, (int)$this->total_quantity - (int)$this->reserved_quantity);
    }

    /**
     * Stock status based on available quantity vs reorder level.
     */
    public function getStatusAttribute(): string
    {
        $available = $this->available_quantity;
        $reorder   = (int)($this->reorder_level ?? 10);

        if ($this->total_quantity <= 0)        return 'Out of Stock';
        if ($available <= 0)                   return 'All Reserved';
        if ($available <= $reorder)            return 'Low Stock';
        return 'In Stock';
    }

    /**
     * CSS-friendly status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Out of Stock'  => 'red',
            'All Reserved'  => 'orange',
            'Low Stock'     => 'amber',
            'In Stock'      => 'green',
            default         => 'gray',
        };
    }

    /**
     * Total monetary value of this stock (total_quantity × unit price).
     */
    public function getStockValueAttribute(): float
    {
        $price = $this->product?->price ?? 0;
        return round((int)$this->total_quantity * (float)$price, 2);
    }

    /**
     * Value of available (sellable) stock.
     */
    public function getAvailableValueAttribute(): float
    {
        $price = $this->product?->price ?? 0;
        return round($this->available_quantity * (float)$price, 2);
    }

    // ─── Helpers ────────────────────────────────────

    public function getIsLowStockAttribute(): bool
    {
        return in_array($this->status, ['Low Stock', 'Out of Stock', 'All Reserved']);
    }

    /**
     * Log a stock movement and update quantities atomically.
     */
    public function logMovement(string $type, int $change, ?int $userId, ?string $refType = null, $refId = null, ?string $refNumber = null, ?string $notes = null): StockMovement
    {
        $before = ($type === 'reserved' || $type === 'released')
            ? $this->reserved_quantity
            : $this->total_quantity;

        $after = $before + $change;

        return StockMovement::create([
            'stock_id'         => $this->id,
            'product_id'       => $this->product_id,
            'warehouse_id'     => $this->warehouse_id,
            'company_id'       => $this->company_id,
            'user_id'          => $userId,
            'type'             => $type,
            'quantity_change'  => $change,
            'quantity_before'  => $before,
            'quantity_after'   => max(0, $after),
            'reference_type'   => $refType,
            'reference_id'     => $refId,
            'reference_number' => $refNumber,
            'notes'            => $notes,
        ]);
    }
}
