<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // Get all locations
    public function index()
    {
        $locations = Location::all();
        return response()->json($locations, 200);
    }

    // Get a single location by route
    public function show($route)
    {
        $location = Location::where('route', $route)->first();
        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }
        return response()->json($location, 200);
    }

    // Add a new location
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:locations',
            'route' => 'required|string|max:255|unique:locations',
        ]);

        $location = Location::create($request->only('title', 'route'));
        return response()->json($location, 201);
    }

    // Update an existing location by ID
    public function update(Request $request, $route)
    {
        $location = Location::where('route', $route)->first();
        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255|unique:locations,title,' . $location->id,
            'route' => 'required|string|max:255|unique:locations,route,' . $location->id,
        ]);

        $location->update($request->only('title', 'route'));
        return response()->json($location, 200);
    }

    // Delete a location by route
    public function destroy($route)
    {
        $location = Location::where('route', $route)->first();
        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        $location->delete();
        return response()->json(null, 204);
    }
}
