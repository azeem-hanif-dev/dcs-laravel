<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['company_id', 'full_name', 'email', 'username', 'password', 'contact_number', 'gender', 'role', 'is_active', 'is_delete', 'address', 'date_of_birth'];
    protected $hidden = ['password'];
    protected $casts = ['is_active' => 'boolean', 'is_delete' => 'boolean', 'date_of_birth' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
