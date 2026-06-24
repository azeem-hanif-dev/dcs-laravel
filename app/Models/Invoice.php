<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Material\Supplier;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'sales_order_id', 'shop_id',
        'purchase_order_id', 'supplier_id', 'invoice_type',
        'invoice_date', 'due_date', 'total_amount', 'paid_amount',
        'status', 'notes', 'company_id', 'user_id'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    // ─── Relationships ──────────────────────────────────

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Accessors ──────────────────────────────────────

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->balance_due > 0;
    }

    // ─── Helpers ────────────────────────────────────────

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ymd');
        $last = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $seq = $last ? (int)substr($last->invoice_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a Procurement invoice number (PINV prefix).
     */
    public static function generateProcurementInvoiceNumber(): string
    {
        $prefix = 'PINV-' . date('Ymd');
        $last = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $seq = $last ? (int)substr($last->invoice_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
