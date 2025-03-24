<?php
namespace App\Http\Controllers;

use App\Models\Webinar;
use Illuminate\Http\Request;

class WebinarController extends Controller
{
    // Get all webinars
    public function index()
    {
        return response()->json(Webinar::all(), 200);
    }

    // Get single webinar
    public function show($id)
    {
        $webinar = Webinar::find($id);
        if (!$webinar) {
            return response()->json(['message' => 'Webinar not found'], 404);
        }
        return response()->json($webinar, 200);
    }

    // Create a webinar
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'registration_link' => 'required|url',
            'featured_img' => 'nullable|string',
            'flyer_url' => 'nullable|string',
        ]);

        $webinar = Webinar::create($validated);

        return response()->json($webinar, 201);
    }

    // Update a webinar
    public function update(Request $request, $id)
    {
        $webinar = Webinar::find($id);
        if (!$webinar) {
            return response()->json(['message' => 'Webinar not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required',
            'registration_link' => 'sometimes|required|url',
            'featured_img' => 'nullable|string',
            'flyer_url' => 'nullable|string',
        ]);

        $webinar->update($validated);

        return response()->json($webinar, 200);
    }

    // Delete a webinar
    public function destroy($id)
    {
        $webinar = Webinar::find($id);
        if (!$webinar) {
            return response()->json(['message' => 'Webinar not found'], 404);
        }

        $webinar->delete();
        return response()->json(['message' => 'Webinar deleted'], 200);
    }
}
