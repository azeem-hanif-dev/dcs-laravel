<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class Subcategory extends Model
{
    protected $fillable = ['name', 'category_id', 'user_id', 'company_id'];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
