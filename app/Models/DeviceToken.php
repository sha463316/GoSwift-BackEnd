<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'device_token'];
    use HasFactory;

    function user()
    {
        return $this->belongsTo(User::class);
    }
}
