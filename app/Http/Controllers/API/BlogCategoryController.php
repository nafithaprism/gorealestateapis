<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    // Get all blog categories
    public function index()
    {
        $blogCategories = BlogCategory::all();
        return response()->json($blogCategories, 200);
    }

    // Get a single blog category by route
        public function show($id)
    {
        $blogCategory = BlogCategory::find($id);

        if (!$blogCategory) {
            return response()->json(['error' => 'Blog category not found'], 404);
        }

        return response()->json($blogCategory, 200);
    }

    // Add a new blog category
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:blog_categories',
            'route' => 'required|string|max:255|unique:blog_categories',
        ]);

        $blogCategory = BlogCategory::create($request->only('title', 'route'));
        return response()->json($blogCategory, 201);
    }

    // Update an existing blog category by route
    public function update(Request $request, $id)
    {
        $blogCategory = BlogCategory::find($id);
        if (!$blogCategory) {
            return response()->json(['error' => 'Blog category not found'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255|unique:blog_categories,title,' . $blogCategory->id,
            'route' => 'required|string|max:255|unique:blog_categories,route,' . $blogCategory->id,
        ]);

        $blogCategory->update($request->only('title', 'route'));
        return response()->json($blogCategory, 200);
    }

    public function destroy($id)
    {
        $blogCategory = BlogCategory::find($id);
        if (!$blogCategory) {
            return response()->json(['error' => 'Blog category not found'], 404);
        }

        $blogCategory->delete();
        return response()->json(null, 204);
    }
}
