<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndUser extends Model
{
    protected $table = 'end_users';
    protected $fillable = ['name', 'email', 'phone_number', 'password', 'otp', 'otp_expires_at', 'address', 'dob'];
    protected $hidden = ['password'];
    protected $casts = ['otp_expires_at' => 'datetime', 'dob' => 'date'];
}
