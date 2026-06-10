<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialOrderItem extends Model
{
    protected $fillable = ['material_order_id', 'material_id', 'quantity', 'supplier_id', 'project_id'];

    public function materialOrder(): BelongsTo { return $this->belongsTo(MaterialOrder::class); }
    public function material(): BelongsTo { return $this->belongsTo(Material::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
}
