<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerForm extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'company', 'phone', 'email',
        'purchase_objective', 'min_budget', 'max_budget', 'message'
    ];
}
