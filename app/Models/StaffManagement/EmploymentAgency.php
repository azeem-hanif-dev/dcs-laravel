<?php

namespace App\Models\StaffManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class EmploymentAgency extends Model
{
    protected $fillable = ['name', 'email', 'contact_person', 'phone', 'country_code', 'country', 'address', 'city', 'url', 'user_id', 'company_id', 'is_active', 'is_delete'];
    protected $casts = ['is_active' => 'boolean', 'is_delete' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
