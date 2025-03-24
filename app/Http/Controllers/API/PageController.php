<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    // Get all pages
    public function index()
    {
        return response()->json(Page::all(), 200);
    }

    // Get a single page by route
    public function show($route)
    {
        $page = Page::where('route', $route)->first();

        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        return response()->json($page, 200);
    }

    // Add a new page
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:pages,name',
            'route' => 'required|string|unique:pages,route',
            'content' => 'nullable|array',
        ]);

        $page = Page::create($validated);

        return response()->json($page, 201);
    }

    // Update an existing page by route
    public function update(Request $request, $route)
    {
        $page = Page::where('route', $route)->first();

        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:pages,name,' . $page->id,
            'route' => 'sometimes|string|unique:pages,route,' . $page->id,
            'content' => 'nullable|array',
        ]);

        $page->update($validated);

        return response()->json($page, 200);
    }

    // Delete a page by id
    public function destroy($id)
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        $page->delete();

        return response()->json(null, 204); // Changed to 204 No Content
    }
}