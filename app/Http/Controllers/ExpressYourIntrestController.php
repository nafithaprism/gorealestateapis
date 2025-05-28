<?php

namespace App\Http\Controllers;

use App\Models\ExpressInterest;
use Illuminate\Http\Request;

class ExpressYourIntrestController extends Controller
{
    public function index()
    {
        return response()->json(ExpressInterest::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'project_id' => 'required|numeric',
            'country_of_residence' => 'nullable|string|max:255',
            'number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'purchase_objective' => 'required|string',
            'budget' => 'nullable|numeric',
            'message' => 'nullable|string',
        ]);

        $interest = ExpressInterest::create($validated);

        return response()->json([
            'message' => 'Interest created successfully.',
            'data' => $interest
        ], 201);
    }

    public function show($id)
    {
        $interest = ExpressInterest::find($id);

        if (!$interest) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        return response()->json($interest);
    }

    public function update(Request $request, $id)
    {
        $interest = ExpressInterest::find($id);

        if (!$interest) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:255',
            'number' => 'sometimes|required|string|max:20',
            'project_id' => 'required|numeric',
            'email' => 'sometimes|required|email|max:255',
            'purchase_objective' => 'sometimes|required|string',
            'budget' => 'nullable|numeric',
            'message' => 'nullable|string',
        ]);

        $interest->update($validated);

        return response()->json([
            'message' => 'Interest updated successfully.',
            'data' => $interest
        ]);
    }

    public function destroy($id)
    {
        $interest = ExpressInterest::find($id);

        if (!$interest) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $interest->delete();

        return response()->json(['message' => 'Interest deleted successfully.']);
    }
}