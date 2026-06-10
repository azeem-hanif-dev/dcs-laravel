<?php

namespace App\Models\StaffManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;

class Designation extends Model
{
    protected $fillable = ['name', 'company_id'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
