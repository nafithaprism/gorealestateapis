<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\IndustryReport;
use Illuminate\Http\Request;

class IndustryReportController extends Controller
{
    // GET - Get all industry reports
    public function index()
    {
        $reports = IndustryReport::all();
        return response()->json([
            'message' => 'Industry reports retrieved successfully',
            'data' => $reports
        ], 200);
    }

    // GET - Get single industry report
    public function show($id)
    {
        $report = IndustryReport::find($id);

        if (!$report) {
            return response()->json(['error' => 'Industry report not found'], 404);
        }

        return response()->json([
            'message' => 'Industry report retrieved successfully',
            'data' => $report
        ], 200);
    }

    // POST - Add new industry report
    public function store(Request $request)
    {
        $request->validate([
            'featured_img' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'required|url|max:255'
        ]);

        $report = IndustryReport::create([
            'featured_img' => $request->featured_img,
            'title' => $request->title,
            'description' => $request->description,
            'url' => $request->url
        ]);

        return response()->json([
            'message' => 'Industry report created successfully',
            'data' => $report
        ], 201);
    }

    // PUT/PATCH - Update industry report
    public function update(Request $request, $id)
    {
        $report = IndustryReport::find($id);

        if (!$report) {
            return response()->json(['error' => 'Industry report not found'], 404);
        }

        $request->validate([
            'featured_img' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'required|url|max:255'
        ]);

        $report->update([
            'featured_img' => $request->featured_img,
            'title' => $request->title,
            'description' => $request->description,
            'url' => $request->url
        ]);

        return response()->json([
            'message' => 'Industry report updated successfully',
            'data' => $report
        ], 200);
    }

    // DELETE - Delete industry report
    public function destroy($id)
    {
        $report = IndustryReport::find($id);

        if (!$report) {
            return response()->json(['error' => 'Industry report not found'], 404);
        }

        $report->delete();

        return response()->json([
            'message' => 'Industry report deleted successfully'
        ], 200);
    }
}
