<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'date', 'posted_by', 'title', 'route', 'long_description',
        'feature_image', 'inner_page_img', 'seo','category_id',
    ];

    protected $casts = [
        'date' => 'date', // Casts date to a Carbon instance
        'seo' => 'array', // Casts JSON to an array
    ];

    // Relationship with BlogCategory
    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }
}