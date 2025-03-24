<?php

// app/Http/Controllers/VideoController.php
namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    // Get all videos
    public function index()
    {
        $videos = Video::all();
        return response()->json([
            'message' => 'Videos retrieved successfully',
            'data' => $videos
        ], 200);
    }

    // Get single video
    public function show($id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'message' => 'Video not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Video retrieved successfully',
            'data' => $video
        ], 200);
    }

    // Add new video
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'thumbnail' => 'nullable|string',
            'title' => 'required|string|max:255',
            'url' => 'required|string|url', // Validates URL format
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $video = Video::create($request->only(['thumbnail', 'title', 'url']));

        return response()->json([
            'message' => 'Video created successfully',
            'data' => $video
        ], 201);
    }

    // Update video
    public function update(Request $request, $id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'message' => 'Video not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'thumbnail' => 'nullable|string',
            'title' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $video->update($request->only(['thumbnail', 'title', 'url']));

        return response()->json([
            'message' => 'Video updated successfully',
            'data' => $video
        ], 200);
    }

    // Delete video
    public function destroy($id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'message' => 'Video not found'
            ], 404);
        }

        $video->delete();

        return response()->json([
            'message' => 'Video deleted successfully'
        ], 200);
    }
}
