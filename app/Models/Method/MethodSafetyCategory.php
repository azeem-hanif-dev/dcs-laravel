<?php

namespace App\Models\Method;

use Illuminate\Database\Eloquent\Model;

class MethodSafetyCategory extends Model
{
    protected $table = 'method_safety_categories';
    protected $fillable = ['name'];
}
