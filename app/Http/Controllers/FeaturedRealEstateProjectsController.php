<?php

namespace App\Http\Controllers;

use App\Models\FeaturedRealEstateProject;
use Illuminate\Http\Request;

class FeaturedRealEstateProjectsController extends Controller
{
    public function index()
    {
        return response()->json(FeaturedRealEstateProject::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_plan' => 'required|string',
            'developer_logo' => 'nullable|string',
            'feature_image' => 'nullable|string',
            'payment_plan' => ['nullable', 'regex:/^\d{1,3}\|\d{1,3}\|\d{1,3}$/'],
            'location' => 'required|string',
            'project_name' => 'required|string',
            'project_developer' => 'required|string',
            'route' => 'required|string',
            'price' => 'required|numeric',
            'project_factsheet' => 'nullable|string',
            'project_go_flyer' => 'nullable|string',
            'inner_page_content' => 'nullable|string',
            'banner_image' => 'nullable|string',
            'content' => 'nullable|string',
             'anticipated_completion_date' => 'nullable|string', 
        ]);

        $project = FeaturedRealEstateProject::create($validated);

        return response()->json([
            'message' => 'Featured real estate project created successfully.',
            'data' => $project
        ], 201);
    }

   public function show($route)
{
    $project = FeaturedRealEstateProject::where('route', $route)->first();

    return $project
        ? response()->json($project)
        : response()->json(['message' => 'Project not found.'], 404);
}
    public function update(Request $request, $route)
{
    $project = FeaturedRealEstateProject::where('route', $route)->first();

    if (!$project) {
        return response()->json(['message' => 'Project not found.'], 404);
    }

    $validated = $request->validate([
        'developer_logo' => 'nullable|string',
        'project_plan' => 'required|string',
        'feature_image' => 'nullable|string',
        'payment_plan' => ['nullable', 'regex:/^\d{1,3}\|\d{1,3}\|\d{1,3}$/'],
        'location' => 'nullable|string',
        'project_name' => 'nullable|string',
        'project_developer' => 'nullable|string',
        'route' => 'required|string', // You can keep this if you allow changing the route
        'price' => 'nullable|numeric',
        'project_factsheet' => 'nullable|string',
        'project_go_flyer' => 'nullable|string',
        'inner_page_content' => 'nullable|string',
        'banner_image' => 'nullable|string',
        'content' => 'nullable|string',
         'anticipated_completion_date' => 'nullable|string', 
    ]);

    // Optional: Handle uniqueness of new route (if it's changing)
    if ($validated['route'] !== $route) {
        $exists = FeaturedRealEstateProject::where('route', $validated['route'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Route must be unique.'], 422);
        }
    }

    $project->update($validated);

    return response()->json([
        'message' => 'Project updated successfully.',
        'data' => $project
    ]);
}


    public function destroy($id)
    {
        $project = FeaturedRealEstateProject::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully.']);
    }
}
