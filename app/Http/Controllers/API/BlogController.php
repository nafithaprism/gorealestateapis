<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // Get all blogs
    public function index()
    {
        $blogs = Blog::with('category')->get();
        return response()->json($blogs, 200);
    }

    // Get a single blog
    public function show($route)
    {
        $blog = Blog::with('category')->where('route', $route)->first();

        if (!$blog) {
            return response()->json(['error' => 'Blog not found'], 404);
        }

        return response()->json($blog, 200);
    }

    // Store a new blog
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'posted_by' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'route' => 'required|string|max:255|unique:blogs',
            'long_description' => 'required|string',
            'feature_image' => 'nullable|string',
            'inner_page_img' => 'nullable|string',
            'seo' => 'nullable|array',
            'category_id' => 'nullable|exists:blog_categories,id',
        ]);

        $blog = Blog::create($request->all());
        return response()->json($blog, 201);
    }

   // Update an existing blog
public function update(Request $request, $route)
{
    // Find the blog by route
    $blog = Blog::where('route', $route)->first();

    if (!$blog) {
        return response()->json(['error' => 'Blog not found'], 404);
    }

    // Validate the request
    $request->validate([
        'date' => 'required|date',
        'posted_by' => 'required|string|max:255',
        'title' => 'required|string|max:255',
        'route' => 'required|string|max:255|unique:blogs,route,' . $blog->id,
        'long_description' => 'required|string',
        'feature_image' => 'nullable|string',
        'inner_page_img' => 'nullable|string',
        'seo' => 'nullable|array',
        'category_id' => 'nullable|exists:blog_categories,id',
    ]);

    // Update the blog
    $blog->update($request->all());

    return response()->json($blog, 200);
}

    // Delete a blog
    public function destroy($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['error' => 'Blog not found'], 404);
        }

        $blog->delete();
        return response()->json(null, 204);
    }
}
