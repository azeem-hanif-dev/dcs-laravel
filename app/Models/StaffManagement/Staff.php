<?php

namespace App\Models\StaffManagement;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class Staff extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'staff';
    protected $fillable = ['user_id', 'company_id', 'name', 'username', 'email', 'employee_code', 'phone', 'designation', 'agency_id', 'job_type_id', 'shift_id', 'permission', 'gender', 'visa_expiry', 'health_expiry', 'passport_expiry', 'password', 'mobile_signup'];
    protected $hidden = ['password'];
    protected $casts = ['permission' => 'array', 'mobile_signup' => 'boolean', 'visa_expiry' => 'date', 'health_expiry' => 'date', 'passport_expiry' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function agency(): BelongsTo { return $this->belongsTo(EmploymentAgency::class, 'agency_id'); }
    public function jobType(): BelongsTo { return $this->belongsTo(StaffRole::class, 'job_type_id'); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
}
