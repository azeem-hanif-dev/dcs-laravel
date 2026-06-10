<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Company\JobDef;
use App\Models\Company\Floor;
use App\Models\Company\Area;
use App\Models\Company\Element;
use App\Models\Company\Task;
use App\Models\StaffManagement\Staff;

class ProjectJob extends Model
{
    protected $table = 'project_jobs';
    protected $fillable = ['user_id', 'company_id', 'job_def_id', 'project_id', 'floor_id', 'area_id', 'element_id', 'task_id', 'worker_id'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function jobDef(): BelongsTo { return $this->belongsTo(JobDef::class, 'job_def_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function floor(): BelongsTo { return $this->belongsTo(Floor::class); }
    public function area(): BelongsTo { return $this->belongsTo(Area::class); }
    public function element(): BelongsTo { return $this->belongsTo(Element::class); }
    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function worker(): BelongsTo { return $this->belongsTo(Staff::class, 'worker_id'); }
    public function jobTasks(): HasMany { return $this->hasMany(ProjectJobTask::class); }
}
