<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class Distributor extends Model
{
    protected $fillable = ['name', 'email', 'contact_person', 'contact_number', 'address', 'supplier_id', 'user_id', 'company_id', 'is_active', 'logo'];
    protected $casts = ['is_active' => 'boolean'];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
