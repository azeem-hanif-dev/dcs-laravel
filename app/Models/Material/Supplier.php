<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;
use App\Models\Company;

class Supplier extends Model
{
    protected $fillable = ['name', 'email', 'contact_person', 'contact_number', 'address', 'company_name', 'tax_number', 'payment_terms', 'user_id', 'company_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function distributors(): HasMany { return $this->hasMany(Distributor::class); }
}
