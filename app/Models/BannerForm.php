<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerForm extends Model
{
    protected $fillable = [
    'first_name',
    'last_name',
    'nationality',
    'country_of_residence',
    'company',
    'number',
    'email',
    'purchase_objective',
    'purchase_primary_goal',
    'budget',
    'message',
    'date',
];
}