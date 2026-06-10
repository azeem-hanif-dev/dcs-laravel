<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;
use App\Models\Company;

class Category extends Model
{
    protected $fillable = ['name', 'user_id', 'company_id'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function subcategories(): HasMany { return $this->hasMany(Subcategory::class); }
    public function materials(): HasMany { return $this->hasMany(Material::class); }
}
