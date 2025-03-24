<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webinar extends Model
{
    use HasFactory;

    protected $fillable = [
        'featured_img', 'title', 'date', 'time', 'registration_link', 'flyer_url'
    ];
}
