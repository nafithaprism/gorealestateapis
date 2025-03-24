<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscribers';

    protected $fillable = ['full_name', 'email', 'is_subscribed', 'subscribed_at'];

    protected $casts = [
        'is_subscribed' => 'boolean',
        'subscribed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
