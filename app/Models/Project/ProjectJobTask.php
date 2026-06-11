<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectJobTask extends Model
{
    protected $fillable = ['project_job_id', 'task_id', 'start_time', 'end_time', 'status'];

    public function projectJob(): BelongsTo { return $this->belongsTo(ProjectJob::class); }
}
