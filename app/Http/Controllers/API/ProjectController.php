<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    // Helper method to format the response
    private function formatProjectResponse($project)
    {
        $data = $project->toArray();

        // If locations has only one item, remove the array wrapper
        if (count($data['locations']) === 1) {
            $data['locations'] = $data['locations'][0];
        }

        // If property_types has only one item, remove the array wrapper
        if (count($data['property_types']) === 1) {
            $data['property_types'] = $data['property_types'][0];
        }
        $data['location_id'] = $project->locations->pluck('id')->first() ?? null; // Single string or null
        $data['property_type_id'] = $project->propertyTypes->pluck('id')->first() ?? null; // Single string or null
        return $data;
    }

    // Get all projects
    public function index()
    {
        $projects = Project::with(['locations', 'propertyTypes'])->get();
        $formattedProjects = $projects->map(function ($project) {
            return $this->formatProjectResponse($project);
        });
        return response()->json($formattedProjects, 200);
    }


    // Get a single project by route
    public function show($route)
    {
        $project = Project::with(['locations', 'propertyTypes'])
            ->where('route', $route)
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $formattedProject = $this->formatProjectResponse($project);
        return response()->json($formattedProject, 200);
    }

    // Add a new project
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:projects',
            'route' => 'required|string|max:255|unique:projects',
            'featured_img' => 'nullable|string',
            'price' => 'required|numeric',
            'is_featured' => 'boolean',
            'area' => 'required|string|max:255',
            'property_for' => 'nullable|string',
            'arabic_flyer' => 'nullable|string',
            'english_flyer' => 'nullable|string',
            'description' => 'nullable|string',
            'location_id' => 'required||string', //
            'location_id.*' => 'exists:locations,id',
            'property_type_id' => 'required|string', //
            'property_type_id.*' => 'exists:property_types,id',
        ]);

        $project = Project::create($request->only(
            'title', 'route', 'featured_img', 'price', 'is_featured', 'area', 'description','property_for', 'arabic_flyer', 'english_flyer'
        ));

        // Attach locations and property types
        $project->locations()->sync($request->location_id);
        $project->propertyTypes()->sync($request->property_type_id);

        return response()->json($project->load('locations', 'propertyTypes'), 201);
    }

    // Update an existing project by ID
    public function update(Request $request, $route)
    {
        $project = Project::where('route', $route)->first();
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255|unique:projects,title,' . $project->id,
            'route' => 'required|string|max:255|unique:projects,route,' . $project->id,
            'featured_img' => 'nullable|string',
            'price' => 'required|numeric',
            'is_featured' => 'boolean',
            'property_for' => 'nullable|string',
            'arabic_flyer' => 'nullable|string',
            'english_flyer' => 'nullable|string',
            'area' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location_id' => 'required|string',
            'location_id.*' => 'exists:locations,id',
            'property_type_id' => 'required|string',
            'property_type_id.*' => 'exists:property_types,id',
        ]);

        $project->update($request->only(
            'title', 'route', 'featured_img', 'price', 'is_featured', 'area', 'description','property_for', 'arabic_flyer', 'english_flyer'
        ));

        // Sync locations and property types
        $project->locations()->sync($request->location_id);
        $project->propertyTypes()->sync($request->property_type_id);

        return response()->json($project->load('locations', 'propertyTypes'), 200);
    }

    // Delete a project by route
    public function destroy($route)
    {
        $project = Project::where('route', $route)->first();
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $project->locations()->detach(); // Remove relationships
        $project->propertyTypes()->detach();
        $project->delete();

        return response()->json(null, 204);
    }
}