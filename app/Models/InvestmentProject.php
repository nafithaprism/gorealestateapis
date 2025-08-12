<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentProject extends Model
{
    protected $fillable = [
        'developer_logo',
        'feature_image',
        // 'payment_plan',
        'location',
        'location_map',
        'project_plan',
        'price',
        // 'project_factsheet',
        // 'project_go_flyer',
        'inner_page_content',
        'banner_image',
        'route',
        // 'project_plan',
        'content',
        'opportunity_type'//added new field now
    ];
}