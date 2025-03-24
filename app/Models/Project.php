<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title', 'route', 'featured_img', 'price', 'is_featured', 'area', 'description',
        'property_for',
        'arabic_flyer',
        'english_flyer',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_project');
    }

    public function propertyTypes()
    {
        return $this->belongsToMany(PropertyType::class, 'project_property_type');
    }
}