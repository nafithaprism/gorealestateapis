<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['title', 'route'];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'location_project');
    }
}
