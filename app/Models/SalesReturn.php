<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    protected $fillable = [
        'return_number', 'sales_order_id', 'shop_id', 'return_date',
        'total_amount', 'status', 'reason', 'notes', 'company_id', 'user_id'
    ];

    protected $casts = ['return_date' => 'date', 'total_amount' => 'decimal:2'];

    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function items(): HasMany { return $this->hasMany(SalesReturnItem::class); }

    public static function generateNumber(): string
    {
        $prefix = 'SR-' . date('Ymd');
        $last = self::where('return_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $seq = $last ? (int)substr($last->return_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
