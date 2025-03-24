<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['name', 'route', 'content'];

    protected $casts = [
        'content' => 'array', // Casts JSON to an array
    ];
}
