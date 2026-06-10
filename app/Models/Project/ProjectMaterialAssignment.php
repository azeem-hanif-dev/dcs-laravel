<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Material\Material;
use App\Models\StaffManagement\Staff;

class ProjectMaterialAssignment extends Model
{
    protected $fillable = ['project_id', 'material_id', 'worker_id', 'category', 'assigned_quantity', 'remaining_quantity', 'daily_consumption', 'used_quantity', 'life_used', 'type', 'expiry_date', 'status', 'returned_quantity', 'notes'];
    protected $casts = ['expiry_date' => 'date'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function material(): BelongsTo { return $this->belongsTo(Material::class); }
    public function worker(): BelongsTo { return $this->belongsTo(Staff::class, 'worker_id'); }
}
