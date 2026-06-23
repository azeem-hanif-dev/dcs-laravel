<?php

namespace App\Models;

use App\Models\Material\Distributor;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    protected $fillable = [
        'company_id', 'distributor_id', 'name', 'full_name', 'email', 'username', 'password',
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

    // ─── Relationships ────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class, 'distributor_id');
    }

    // ─── Role Checks ──────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDistributor(): bool
    {
        return $this->role === 'distributor';
    }

    /**
     * Appended accessor for API responses – the list of module keys.
     */
    protected function getAllowedModulesAttribute(): array
    {
        return $this->getAllowedModules();
    }

    /**
     * Users that can log into the web portal.
     */
    public function canLogin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'distributor']);
    }

    /**
     * Check if the user has access to a given top-level module.
     *
     * Module key examples: 'sales', 'inventory', 'procurement', 'customers', 'reports'
     */
    public function hasModuleAccess(string $module): bool
    {
        // Dashboard is always accessible
        if ($module === 'dashboard') {
            return true;
        }

        // Super admins and company admins have full access
        if (in_array($this->role, ['superadmin', 'admin'])) {
            return true;
        }

        // Role-based default grants (preserve pre-existing access for legacy roles)
        $roleDefaults = [
            'salesman'          => ['sales'],
            'warehouse_manager' => ['inventory'],
        ];
        if (in_array($module, $roleDefaults[$this->role] ?? [])) {
            return true;
        }

        // Distributor users: check distributor-level module_permissions
        if ($this->role === 'distributor') {
            if ($this->distributor_relation?->module_permissions) {
                return (bool) ($this->distributor_relation->module_permissions[$module] ?? false);
            }
            // Fallback to user-level permissions
            return (bool) ($this->permissions['modules'][$module] ?? false);
        }

        // Other roles: check user-level permissions
        return (bool) ($this->permissions['modules'][$module] ?? false);
    }

    /**
     * Get the list of module keys the user is allowed to access.
     */
    public function getAllowedModules(): array
    {
        // Super admins and company admins see everything
        if (in_array($this->role, ['superadmin', 'admin'])) {
            return ['dashboard', 'sales', 'inventory', 'procurement', 'customers', 'reports'];
        }

        // Distributor: load from distributor record or user permissions
        if ($this->role === 'distributor') {
            $src = $this->distributor_relation?->module_permissions
                ?? $this->permissions['modules']
                ?? $this->getDefaultDistributorModules();
            $modules = array_keys(array_filter($src, fn($v) => (bool) $v));
            // Dashboard is always available
            if (!in_array('dashboard', $modules)) {
                array_unshift($modules, 'dashboard');
            }
            return $modules;
        }

        // Other roles
        $src = $this->permissions['modules'] ?? [];
        $modules = array_keys(array_filter($src, fn($v) => (bool) $v));
        if (!in_array('dashboard', $modules)) {
            array_unshift($modules, 'dashboard');
        }
        return $modules;
    }

    /**
     * Default modules for a newly created distributor.
     */
    public static function getDefaultDistributorModules(): array
    {
        return [
            'sales'        => true,
            'inventory'    => false,
            'procurement'  => true,
            'customers'    => true,
            'reports'      => false,
        ];
    }

    // ─── Internal: eager-load-safe distributor accessor ───

    protected function getDistributorRelationAttribute()
    {
        if (!$this->relationLoaded('distributor')) {
            $this->setRelation('distributor', $this->distributor()->first());
        }
        return $this->getRelation('distributor');
    }
}
