<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpressInterest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'nationality',
        'country_of_residence',
        'number',
        'email',
        'purchase_objective',
        'budget',
        'message',
        'project_id',
    ];


    public function project()
    {
        return $this->belongsTo(FeaturedRealEstateProject::class, 'project_id');
    }
}