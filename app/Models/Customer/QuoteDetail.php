<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StaffManagement\Staff;

class QuoteDetail extends Model
{
    protected $fillable = ['quotation_id', 'worker_id', 'total_workers', 'rate', 'hours', 'days', 'extra_hours', 'discount', 'net_rate', 'price'];
    protected $casts = ['total_workers' => 'integer', 'rate' => 'decimal:2', 'hours' => 'integer', 'days' => 'integer', 'extra_hours' => 'integer', 'discount' => 'decimal:2', 'net_rate' => 'decimal:2', 'price' => 'decimal:2'];

    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function worker(): BelongsTo { return $this->belongsTo(Staff::class, 'worker_id'); }
}
