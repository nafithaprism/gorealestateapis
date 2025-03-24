<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    // Get all property types
    public function index()
    {
        $propertyTypes = PropertyType::all();
        return response()->json($propertyTypes, 200);
    }

   // Get a single property type by route
   public function show($route)
   {
       $propertyType = PropertyType::where('route', $route)->first();
       if (!$propertyType) {
           return response()->json(['error' => 'Property type not found'], 404);
       }
       return response()->json($propertyType, 200);
   }

    // Add a new property type
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:property_types',
            'route' => 'required|string|max:255|unique:property_types',
        ]);

        $propertyType = PropertyType::create($request->only('title', 'route'));
        return response()->json($propertyType, 201);
    }

    // Update an existing property type
    public function update(Request $request, $route)
    {
        $propertyType = PropertyType::where('route', $route)->first();

        if (!$propertyType) {
            return response()->json(['error' => 'Property type not found'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255|unique:property_types,title,' . $propertyType->id,
            'route' => 'required|string|max:255|unique:property_types,route,' . $propertyType->id,
        ]);

        $propertyType->update($request->only('title', 'route'));

        return response()->json($propertyType, 200);
    }

   // Delete a property type by route
   public function destroy($route)
   {
       $propertyType = PropertyType::where('route', $route)->first();
       if (!$propertyType) {
           return response()->json(['error' => 'Property type not found'], 404);
       }

       $propertyType->delete();
       return response()->json(null, 204);
   }
}
