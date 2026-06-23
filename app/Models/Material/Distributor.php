<?php

namespace App\Models\Material;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Models\Company;

class Distributor extends Model
{
    protected $fillable = [
        'name', 'email', 'contact_person', 'contact_number', 'address',
        'supplier_id', 'user_id', 'company_id', 'is_active', 'logo',
        'module_permissions',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'module_permissions' => 'array',
    ];

    // ─── Relationships ────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The login account for this distributor (the users row with role='distributor').
     */
    public function loginUser(): HasOne
    {
        return $this->hasOne(User::class, 'distributor_id');
    }

    // ─── Helpers ───────────────────────────────────────

    /**
     * Create or update the distributor's login user account.
     */
    public function syncLoginUser(array $credentials): User
    {
        $user = $this->loginUser;

        if ($user) {
            $user->update($credentials);
        } else {
            $user = User::create(array_merge($credentials, [
                'distributor_id' => $this->id,
                'company_id'     => $this->company_id,
                'role'           => 'distributor',
                'is_active'      => true,
            ]));
        }

        return $user;
    }
}
