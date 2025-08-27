<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentProject extends Model
{
    protected $fillable = [
        'developer_logo',
        'feature_image',
        'location',
        'location_map',
        'project_plan',
        'price',
        'inner_page_content',
        'banner_image',
        'route',       
        'content',
        'opportunity_type'
    ];
}