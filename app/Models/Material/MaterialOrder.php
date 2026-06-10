<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;
use App\Models\Company;

class MaterialOrder extends Model
{
    protected $fillable = ['company_id', 'ordered_by', 'order_date', 'status', 'notes'];
    protected $casts = ['order_date' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function orderedBy(): BelongsTo { return $this->belongsTo(Admin::class, 'ordered_by'); }
    public function items(): HasMany { return $this->hasMany(MaterialOrderItem::class); }
}
