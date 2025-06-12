<?php

namespace App\Http\Controllers;

use App\Models\CommercialProject;
use Illuminate\Http\Request;

class CommercialProjectsController extends Controller
{
    public function index()
    {
        return response()->json(CommercialProject::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'developer_logo' => 'nullable|string',
            'feature_image' => 'nullable|string',
            'location' => 'required|string',
            'location_map' => 'required|string',
            'project_plan1' => 'required|string',
            'project_plan2' => 'required|string',
            'route' => 'required|string|unique:commercial_projects',
            'price' => 'required|numeric',
            'inner_page_content' => 'nullable|string',
            'banner_image' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        $project = CommercialProject::create($validated);

        return response()->json([
            'message' => 'Commercial project created successfully.',
            'data' => $project
        ], 201);
    }

    public function show($route)
    {
        $project = CommercialProject::where('route', $route)->first();

        return $project
            ? response()->json($project)
            : response()->json(['message' => 'Project not found.'], 404);
    }

    public function update(Request $request, $id)
    {
        $project = CommercialProject::where('id', $id)->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'developer_logo' => 'nullable|string',
            'feature_image' => 'nullable|string',
            'location' => 'nullable|string',
            'location_map' => 'nullable|string',
            'project_plan1' => 'nullable|string',
            'project_plan2' => 'nullable|string',
            'price' => 'nullable|numeric',
            'inner_page_content' => 'nullable|string',
            'banner_image' => 'nullable|string',
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
        $project = CommercialProject::where('route', operator: $id)->first();

        if (!$project) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully.']);
    }
}