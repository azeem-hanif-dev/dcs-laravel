<?php

namespace App\Models\Work;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StaffManagement\Staff;

class WorkCheck extends Model
{
    protected $fillable = ['work_plan_id', 'worker_id', 'date', 'week_number', 'day_of_week', 'check_in_time', 'check_out_time', 'incomplete_check_out', 'status', 'notes', 'location'];
    protected $casts = ['date' => 'date', 'check_in_time' => 'datetime', 'check_out_time' => 'datetime', 'incomplete_check_out' => 'boolean', 'location' => 'array'];

    public function workPlan(): BelongsTo { return $this->belongsTo(WorkPlan::class); }
    public function worker(): BelongsTo { return $this->belongsTo(Staff::class, 'worker_id'); }
}
