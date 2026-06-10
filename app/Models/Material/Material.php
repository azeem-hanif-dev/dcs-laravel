<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class Material extends Model
{
    protected $fillable = ['material_name', 'category_id', 'subcategory_id', 'price', 'total_quantity', 'assigned_quantity', 'description', 'status', 'uses', 'supplier_id', 'user_id', 'company_id'];
    protected $casts = ['price' => 'decimal:2', 'uses' => 'integer'];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subcategory(): BelongsTo { return $this->belongsTo(Subcategory::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
