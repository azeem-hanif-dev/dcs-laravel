<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company as CompanyModel;

class JobDef extends Model
{
    protected $table = 'job_defs';
    protected $fillable = ['name', 'company_id', 'user_id'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(CompanyModel::class); }
}
