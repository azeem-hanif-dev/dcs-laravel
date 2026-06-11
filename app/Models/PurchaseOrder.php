<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Material\Supplier;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number', 'supplier_id', 'warehouse_id', 'company_id',
        'ordered_by', 'order_date', 'expected_date', 'status', 'notes'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'ordered_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public static function generatePoNumber(): string
    {
        $prefix = 'PO-' . date('Ymd');
        $last = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $seq = $last ? (int)substr($last->po_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
