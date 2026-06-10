<?php

namespace App\Models\Work;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Project\Project;
use App\Models\Project\ProjectJob;
use App\Models\StaffManagement\Staff;

class WorkPlan extends Model
{
    protected $fillable = ['user_id', 'company_id', 'project_id', 'job_id', 'worker_id', 'job_type', 'days', 'weeks', 'date', 'status'];
    protected $casts = ['days' => 'array', 'weeks' => 'array', 'date' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function job(): BelongsTo { return $this->belongsTo(ProjectJob::class, 'job_id'); }
    public function worker(): BelongsTo { return $this->belongsTo(Staff::class, 'worker_id'); }
}
