<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    public function index()
    {
        $caseStudies = CaseStudy::all();
        return response()->json([
            'message' => 'Case studies retrieved successfully',
            'data' => $caseStudies
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'featured_img' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'required|string|max:255'
        ]);

        $caseStudy = CaseStudy::create([
            'featured_img' => $request->featured_img,
            'title' => $request->title,
            'description' => $request->description,
            'url' => $request->url
        ]);

        return response()->json([
            'message' => 'Case study created successfully',
            'data' => $caseStudy
        ], 201);
    }

    public function show(CaseStudy $caseStudy)
    {
        return response()->json([
            'message' => 'Case study retrieved successfully',
            'data' => $caseStudy
        ], 200);
    }

    public function update(Request $request, CaseStudy $caseStudy)
    {
        $request->validate([
            'featured_img' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'required|string|max:255'
        ]);

        $caseStudy->update([
            'featured_img' => $request->featured_img,
            'title' => $request->title,
            'description' => $request->description,
            'url' => $request->url
        ]);

        return response()->json([
            'message' => 'Case study updated successfully',
            'data' => $caseStudy
        ], 200);
    }

    public function destroy(CaseStudy $caseStudy)
    {
        $caseStudy->delete();
        return response()->json([
            'message' => 'Case study deleted successfully'
        ], 200);
    }
}
