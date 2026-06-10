<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name', 'is_active', 'is_delete'];
    protected $casts = ['is_active' => 'boolean', 'is_delete' => 'boolean'];

    public function admins(): HasMany { return $this->hasMany(Admin::class); }
    public function staff(): HasMany { return $this->hasMany(StaffManagement\Staff::class); }
    public function customers(): HasMany { return $this->hasMany(Customer\Customer::class); }
    public function projects(): HasMany { return $this->hasMany(Project\Project::class); }
    public function floors(): HasMany { return $this->hasMany(Company\Floor::class); }
    public function areas(): HasMany { return $this->hasMany(Company\Area::class); }
    public function elements(): HasMany { return $this->hasMany(Company\Element::class); }
    public function tasks(): HasMany { return $this->hasMany(Company\Task::class); }
    public function jobDefs(): HasMany { return $this->hasMany(Company\JobDef::class); }
}
