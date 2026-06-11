<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    protected $fillable = [
        'company_id', 'name', 'full_name', 'email', 'username', 'password',
        'phone', 'contact_number', 'role', 'is_active', 'is_delete', 'address',
        'gender', 'date_of_birth',
        'credit_limit', 'territory', 'target_amount',
        'designation', 'employee_code', 'permissions', 'user_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
        'permissions' => 'array',
        'credit_limit' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'password' => 'hashed',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function canLogin(): bool { return in_array($this->role, ['superadmin', 'admin']); }
}
