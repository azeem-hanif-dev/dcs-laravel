<?php

namespace App\Models\StaffManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class StaffRole extends Model
{
    protected $fillable = ['user_id', 'company_id', 'name'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
