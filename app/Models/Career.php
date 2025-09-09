<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'message',
        'country_of_residence',
        'nationality',
        'referel_source',
        'linkedin_account',
        'instagram_account',
    ];
}