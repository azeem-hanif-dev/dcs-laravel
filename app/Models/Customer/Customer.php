<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'contact_person1', 'contact_person2', 'phone', 'country_code', 'country', 'address', 'city', 'password', 'user_id', 'company_id', 'is_active', 'is_delete'];
    protected $hidden = ['password'];
    protected $casts = ['is_active' => 'boolean', 'is_delete' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
