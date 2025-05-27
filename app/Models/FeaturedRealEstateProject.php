<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedRealEstateProject extends Model
{
    protected $fillable = [
        'developer_logo',
        'feature_image',
        'payment_plan',
        'location',
        'project_name',
        'project_developer',
        'price',
        'project_factsheet',
        'project_go_flyer',
        'inner_page_content',
        'banner_image',
        'content',
    ];
}
