<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logger extends Model
{
    protected $fillable = ['method', 'route', 'user', 'username', 'ip', 'timestamp', 'status_code'];
    public $timestamps = false;
}
