<?php

// app/Models/Video.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'thumbnail',
        'title',
        'url',
    ];
}
