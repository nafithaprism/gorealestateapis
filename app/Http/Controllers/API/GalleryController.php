<?php

// namespace App\Http\Controllers\API;

// use App\Http\Controllers\Controller;
// use App\Models\Gallery;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Log;
// use GuzzleHttp\Psr7\Stream;

// class GalleryController extends Controller
// {
//     // Get all gallery entries
//     public function index()
//     {
//         $galleries = Gallery::all();
//         return response()->json($galleries->toArray(), 200); // Convert to array for safe JSON encoding
//     }

//     public function store(Request $request)
// {
//     $request->validate([
//         'alt_tag' => 'required|string|max:255',
//         'files' => 'required',
//         'files.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,webp|max:10240',
//     ]);

//     try {
//         // Get the files input and normalize to an array
//         $filesInput = $request->file('files');
//         $files = is_array($filesInput) ? $filesInput : [$filesInput]; // Single file becomes array

//         // Log the request for debugging
//         Log::info('Gallery store request', [
//             'alt_tag' => $request->input('alt_tag'),
//             'files_count' => count($files),
//             'file_keys' => array_keys($files),
//             'file_names' => array_map(fn($file) => $file->getClientOriginalName(), $files),
//         ]);

//         if (empty($files)) {
//             return response()->json(['error' => 'No files provided'], 400);
//         }

//         $apiKey = env('BUNNYCDN_API_KEY');
//         $storageZone = env('BUNNYCDN_STORAGE_ZONE');
//         $hostname = env('BUNNYCDN_HOSTNAME');
//         $pullZone = filter_var(env('BUNNYCDN_PULL_ZONE'), FILTER_SANITIZE_URL) ?: 'https://default.b-cdn.net';
//         $altTag = mb_convert_encoding($request->alt_tag, 'UTF-8', 'auto') ?: 'Invalid UTF-8 Input';

//         $uploadedFiles = [];
//         $timestamp = time(); // Use a single timestamp for this request

//         foreach ($files as $index => $file) {
//             // Sanitize file name and ensure uniqueness
//             $originalName = preg_replace('/[^A-Za-z0-9\-\.]/', '', $file->getClientOriginalName());
//             $fileName = 'Gallery/' . $timestamp . '-' . $index . '-' . $originalName;
//             $url = "https://{$hostname}/{$storageZone}/{$fileName}";

//             // Log file details
//             Log::info('Processing file', [
//                 'index' => $index,
//                 'original_name' => $file->getClientOriginalName(),
//                 'file_name' => $fileName,
//                 'mime_type' => $file->getClientMimeType(),
//                 'size' => $file->getSize(),
//             ]);

//             // Determine content type
//             $mimeType = $file->getClientMimeType();
//             $contentType = in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])
//                 ? 'application/octet-stream'
//                 : 'application/pdf';

//             // Open a stream to the file
//             $resource = fopen($file->getRealPath(), 'r');
//             $stream = new Stream($resource);

//             // Upload to BunnyCDN
//             $response = Http::withHeaders([
//                 'AccessKey' => $apiKey,
//                 'Content-Type' => $contentType,
//             ])
//             ->withOptions(['allow_redirects' => false])
//             ->withBody($stream, $contentType)
//             ->put($url);

//             fclose($resource);

//             // Log upload result
//             Log::info('BunnyCDN upload attempt', [
//                 'file' => $fileName,
//                 'status' => $response->status(),
//                 'body' => $response->body(),
//             ]);

//             if ($response->status() !== 201) {
//                 Log::error('BunnyCDN upload failed for file', [
//                     'file' => $fileName,
//                     'status' => $response->status(),
//                     'body' => $response->body(),
//                 ]);
//                 continue;
//             }

//             // Save metadata to database
//             $gallery = Gallery::create([
//                 'alt_tag' => $altTag,
//                 'url' => $fileName,
//             ]);

//             // Add to response array
//             $uploadedFiles[] = [
//                 'gallery' => [
//                     'id' => $gallery->id,
//                     'alt_tag' => $gallery->alt_tag,
//                     'url' => $gallery->url,
//                     'created_at' => $gallery->created_at->toIso8601String(),
//                     'updated_at' => $gallery->updated_at->toIso8601String(),
//                 ],
//                 'public_url' => rtrim($pullZone, '/') . '/' . $fileName,
//             ];
//         }

//         if (empty($uploadedFiles)) {
//             return response()->json(['error' => 'No files were uploaded successfully'], 500);
//         }

//         $responseData = [
//             'uploaded_files' => $uploadedFiles,
//             'message' => 'Files uploaded successfully',
//         ];

//         Log::info('Gallery store success', ['response_data' => $responseData]);

//         return response()->json($responseData, 201);
//     } catch (\Exception $e) {
//         Log::error('Gallery store error', [
//             'message' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//             'alt_tag' => $request->input('alt_tag'),
//         ]);
//         return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
//     }
// }

//     // Delete gallery entry and BunnyCDN file
//     public function destroy($id)
//     {
//         $gallery = Gallery::find($id);
//         if (!$gallery) {
//             return response()->json(['error' => 'Gallery entry not found'], 404);
//         }

//         try {
//             $apiKey = env('BUNNYCDN_API_KEY');
//             $storageZone = env('BUNNYCDN_STORAGE_ZONE');
//             $hostname = env('BUNNYCDN_HOSTNAME');
//             $url = "https://{$hostname}/{$storageZone}/{$gallery->url}";

//             $response = Http::withHeaders([
//                 'AccessKey' => $apiKey,
//             ])->delete($url);

//             if ($response->status() !== 200) {
//                 Log::error('BunnyCDN delete failed', [
//                     'status' => $response->status(),
//                     'body' => $response->body(),
//                 ]);
//                 return response()->json(['error' => 'Failed to delete from BunnyCDN'], 500);
//             }

//             $gallery->delete();
//             return response()->json(null, 204);
//         } catch (\Exception $e) {
//             Log::error('Gallery delete error', ['message' => $e->getMessage()]);
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
//     }
// }

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Psr7\Stream;

class GalleryController extends Controller
{
    // Get all gallery entries (unchanged)
    public function index()
{
    $galleries = Gallery::orderBy('created_at', 'desc')->get();
    return response()->json($galleries->toArray(), 200);
}

    public function store(Request $request)
    {
        $request->validate([
            'alt_tag' => 'required|string|max:255',
            'files' => 'required',
            // Updated mimes to include common video formats
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,webp,mp4,mov,avi,wmv,mpeg|max:102400', // Increased max size to 100MB
        ]);

        try {
            $filesInput = $request->file('files');
            $files = is_array($filesInput) ? $filesInput : [$filesInput];

            Log::info('Gallery store request', [
                'alt_tag' => $request->input('alt_tag'),
                'files_count' => count($files),
                'file_names' => array_map(fn($file) => $file->getClientOriginalName(), $files),
            ]);

            if (empty($files)) {
                return response()->json(['error' => 'No files provided'], 400);
            }

            $apiKey = env('BUNNYCDN_API_KEY');
            $storageZone = env('BUNNYCDN_STORAGE_ZONE');
            $hostname = env('BUNNYCDN_HOSTNAME');
            $pullZone = filter_var(env('BUNNYCDN_PULL_ZONE'), FILTER_SANITIZE_URL) ?: 'https://default.b-cdn.net';
            $altTag = mb_convert_encoding($request->alt_tag, 'UTF-8', 'auto') ?: 'Invalid UTF-8 Input';

            $uploadedFiles = [];
            $timestamp = time();

            foreach ($files as $index => $file) {
                $originalName = preg_replace('/[^A-Za-z0-9\-\.]/', '', $file->getClientOriginalName());
                $fileName = 'Gallery/' . $timestamp . '-' . $index . '-' . $originalName;
                $url = "https://{$hostname}/{$storageZone}/{$fileName}";

                Log::info('Processing file', [
                    'index' => $index,
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => $fileName,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);

                // Determine content type based on file mime type
                $mimeType = $file->getClientMimeType();
                $contentType = match(true) {
                    str_contains($mimeType, 'image/') => 'application/octet-stream',
                    str_contains($mimeType, 'video/') => $mimeType, // Use actual video mime type
                    $mimeType === 'application/pdf' => 'application/pdf',
                    default => 'application/octet-stream',
                };

                $resource = fopen($file->getRealPath(), 'r');
                $stream = new Stream($resource);

                $response = Http::withHeaders([
                    'AccessKey' => $apiKey,
                    'Content-Type' => $contentType,
                ])
                ->withOptions(['allow_redirects' => false])
                ->withBody($stream, $contentType)
                ->put($url);

                fclose($resource);

                Log::info('BunnyCDN upload attempt', [
                    'file' => $fileName,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($response->status() !== 201) {
                    Log::error('BunnyCDN upload failed for file', [
                        'file' => $fileName,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                $gallery = Gallery::create([
                    'alt_tag' => $altTag,
                    'url' => $fileName,
                ]);

                $uploadedFiles[] = [
                    'gallery' => [
                        'id' => $gallery->id,
                        'alt_tag' => $gallery->alt_tag,
                        'url' => $gallery->url,
                        'created_at' => $gallery->created_at->toIso8601String(),
                        'updated_at' => $gallery->updated_at->toIso8601String(),
                    ],
                    'public_url' => rtrim($pullZone, '/') . '/' . $fileName,
                    'type' => str_contains($mimeType, 'video/') ? 'video' : 'image' // Add type indicator
                ];
            }

            if (empty($uploadedFiles)) {
                return response()->json(['error' => 'No files were uploaded successfully'], 500);
            }

            $responseData = [
                'uploaded_files' => $uploadedFiles,
                'message' => 'Files uploaded successfully',
            ];

            Log::info('Gallery store success', ['response_data' => $responseData]);

            return response()->json($responseData, 201);
        } catch (\Exception $e) {
            Log::error('Gallery store error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // Delete gallery entry and BunnyCDN file (unchanged)
    public function destroy($id)
    {
        $gallery = Gallery::find($id);
        if (!$gallery) {
            return response()->json(['error' => 'Gallery entry not found'], 404);
        }

        try {
            $apiKey = env('BUNNYCDN_API_KEY');
            $storageZone = env('BUNNYCDN_STORAGE_ZONE');
            $hostname = env('BUNNYCDN_HOSTNAME');
            $url = "https://{$hostname}/{$storageZone}/{$gallery->url}";

            $response = Http::withHeaders([
                'AccessKey' => $apiKey,
            ])->delete($url);

            if ($response->status() !== 200) {
                Log::error('BunnyCDN delete failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to delete from BunnyCDN'], 500);
            }

            $gallery->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Gallery delete error', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}