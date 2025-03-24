<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    protected $fillable = ['title', 'route'];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_property_type');
    }
}