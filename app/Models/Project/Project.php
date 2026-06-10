<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Customer\Customer;
use App\Models\StaffManagement\Staff;

class Project extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'customer_id', 'country_code', 'phone', 'supervisor_id', 'start_date', 'end_date', 'project_code', 'breaktime', 'location_url', 'company_id'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'breaktime' => 'integer'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function supervisor(): BelongsTo { return $this->belongsTo(Staff::class, 'supervisor_id'); }
    public function jobs(): HasMany { return $this->hasMany(ProjectJob::class); }
}
