<?php

namespace App\Models\Method;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;

class Method extends Model
{
    protected $fillable = ['title', 'description', 'video_link', 'is_active', 'category_id', 'company_id'];
    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo { return $this->belongsTo(MethodCategory::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
