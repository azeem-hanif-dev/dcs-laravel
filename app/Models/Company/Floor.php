<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;
use App\Models\Company as CompanyModel;

class Floor extends Model
{
    protected $fillable = ['user_id', 'company_id', 'name'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(CompanyModel::class); }
}
