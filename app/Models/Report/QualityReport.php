<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Project\Project;
use App\Models\Company\Task;
use App\Models\StaffManagement\Staff;
use App\Models\Company;

class QualityReport extends Model
{
    protected $fillable = ['project_id', 'task_id', 'worker_id', 'reviewer_id', 'rating', 'comments', 'status', 'type', 'company_id'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function worker(): BelongsTo { return $this->belongsTo(Staff::class, 'worker_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(Staff::class, 'reviewer_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
