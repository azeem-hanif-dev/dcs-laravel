<?php

namespace App\Models\StaffManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company;

class Shift extends Model
{
    protected $fillable = ['user_id', 'company_id', 'title', 'description', 'start_time', 'end_time', 'time_cushion', 'total_time'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
