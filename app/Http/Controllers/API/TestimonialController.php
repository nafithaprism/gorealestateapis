<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    // Get all testimonials
    public function index()
    {
        $testimonials = Testimonial::all();
        return response()->json($testimonials, 200);
    }

    // Get a single testimonial by id
    public function show($id)
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) {
            return response()->json(['error' => 'Testimonial not found'], 404);
        }
        return response()->json($testimonial, 200);
    }

    // Add a new testimonial
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'user_img' => 'nullable|string',
            'review' => 'required|string',
            'type' => 'required|string',
            'video_url' => 'nullable|string',

        ]);

        $testimonial = Testimonial::create($request->only('name', 'designation', 'user_img', 'review','type','video_url'));
        return response()->json($testimonial, 201);
    }

    // Update an existing testimonial by id
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) {
            return response()->json(['error' => 'Testimonial not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'user_img' => 'nullable|string',
            'review' => 'required|string',
            'video_url' => 'nullable|string',
            'type' => 'required|string',

        ]);

        $testimonial->update($request->only('name', 'designation', 'user_img', 'review','type','video_url'));
        return response()->json($testimonial, 200);
    }

    // Delete a testimonial by id
    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) {
            return response()->json(['error' => 'Testimonial not found'], 404);
        }

        $testimonial->delete();
        return response()->json(null, 204);
    }
}