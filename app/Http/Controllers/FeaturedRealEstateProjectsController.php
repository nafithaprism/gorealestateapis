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
            'developer_logo' => 'nullable|string',
            'feature_image' => 'nullable|string',
            'payment_plan' => ['nullable', 'regex:/^\d{1,3}\|\d{1,3}\|\d{1,3}$/'],
            'location' => 'required|string',
            'project_name' => 'required|string',
            'project_developer' => 'required|string',
            'price' => 'required|numeric',
            'project_factsheet' => 'nullable|url',
            'project_go_flyer' => 'nullable|url',
            'inner_page_content' => 'nullable|string',
            'banner_image' => 'nullable|url',
            'content' => 'nullable|string',
        ]);

        $project = FeaturedRealEstateProject::create($validated);

        return response()->json([
            'message' => 'Featured real estate project created successfully.',
            'data' => $project
        ], 201);
    }

    public function show($id)
    {
        $project = FeaturedRealEstateProject::find($id);
        return $project
            ? response()->json($project)
            : response()->json(['message' => 'Project not found.'], 404);
    }

    public function update(Request $request, $id)
    {
        $project = FeaturedRealEstateProject::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'developer_logo' => 'nullable|string',
            'feature_image' => 'nullable|string',
            'payment_plan' => ['nullable', 'regex:/^\d{1,3}\|\d{1,3}\|\d{1,3}$/'],
            'location' => 'nullable|string',
            'project_name' => 'nullable|string',
            'project_developer' => 'nullable|string',
            'price' => 'nullable|numeric',
            'project_factsheet' => 'nullable|url',
            'project_go_flyer' => 'nullable|url',
            'inner_page_content' => 'nullable|string',
            'banner_image' => 'nullable|url',
            'content' => 'nullable|string',
        ]);

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
