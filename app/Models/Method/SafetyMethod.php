<?php

namespace App\Models\Method;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;

class SafetyMethod extends Model
{
    protected $table = 'safety_methods';
    protected $fillable = ['title', 'description', 'video_link', 'is_active', 'category_id', 'company_id'];
    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo { return $this->belongsTo(MethodSafetyCategory::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
