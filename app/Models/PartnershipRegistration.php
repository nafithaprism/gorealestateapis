<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipRegistration extends Model
{
    protected $fillable = [
        'full_name',
        'mobile_number',
        'country_of_residency',
        'nationality',
        'email',
        'referral_source',
        'payment_option',
    ];
}
