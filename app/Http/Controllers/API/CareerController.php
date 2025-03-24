<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    // POST - Store a new career form submission
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        $career = Career::create([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message
        ]);

        return response()->json([
            'message' => 'Career form submitted successfully',
            'data' => $career
        ], 201);
    }

    // GET - Retrieve all career submissions
    public function index()
    {
        $careers = Career::all();
        return response()->json([
            'message' => 'Career submissions retrieved successfully',
            'data' => $careers
        ], 200);
    }

    // GET - Retrieve a single career submission by ID
    public function show($id)
    {
        $career = Career::find($id);

        if (!$career) {
            return response()->json(['error' => 'Career submission not found'], 404);
        }

        return response()->json([
            'message' => 'Career submission retrieved successfully',
            'data' => $career
        ], 200);
    }

    // DELETE - Remove a career submission
    public function destroy($id)
    {
        $career = Career::find($id);

        if (!$career) {
            return response()->json(['error' => 'Career submission not found'], 404);
        }

        $career->delete();

        return response()->json([
            'message' => 'Career submission deleted successfully'
        ], 200);
    }
}
