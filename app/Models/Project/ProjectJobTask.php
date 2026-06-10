<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company\Task;

class ProjectJobTask extends Model
{
    protected $fillable = ['project_job_id', 'task_id', 'start_time', 'end_time', 'status'];

    public function projectJob(): BelongsTo { return $this->belongsTo(ProjectJob::class); }
    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
}
