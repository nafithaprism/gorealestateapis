<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    // GET - Get all partners
    // public function index()
    // {
    //     $partners = Partner::all();
    //     return response()->json([
    //         'message' => 'Partners retrieved successfully',
    //         'data' => $partners
    //     ], 200);
    // }

    // GET - Get single partner
    public function show($id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json(['error' => 'Partner not found'], 404);
        }

        return response()->json([
            'message' => 'Partner retrieved successfully',
            'data' => $partner
        ], 200);
    }

    // POST - Add new partner
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|string|max:255' // Adjust validation based on your logo storage method
        ]);

        $partner = Partner::create([
            'name' => $request->name,
            'logo' => $request->logo
        ]);

        return response()->json([
            'message' => 'Partner created successfully',
            'data' => $partner
        ], 201);
    }

    // PUT/PATCH - Update partner
    public function update(Request $request, $id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json(['error' => 'Partner not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|string|max:255'
        ]);

        $partner->update([
            'name' => $request->name,
            'logo' => $request->logo
        ]);

        return response()->json([
            'message' => 'Partner updated successfully',
            'data' => $partner
        ], 200);
    }

    // DELETE - Delete partner
    public function destroy($id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json(['error' => 'Partner not found'], 404);
        }

        $partner->delete();

        return response()->json([
            'message' => 'Partner deleted successfully'
        ], 200);
    }
}
