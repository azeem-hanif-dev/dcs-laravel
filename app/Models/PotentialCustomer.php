<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotentialCustomer extends Model
{
    protected $fillable = ['email', 'source', 'visit_count'];
    protected $casts = ['visit_count' => 'integer'];
}
