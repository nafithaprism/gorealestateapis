<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SubscriberController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name'            => ['required','string','max:150'],
            'email'                => ['required','email','max:255','unique:subscribers,email'],
            'mobile_number'        => ['nullable','string','max:30'],
            'country_of_residency' => ['nullable','string','max:120'],
            'nationality'          => ['nullable','string','max:120'],
            'referral_source'      => ['nullable','string','max:150'],
        ]);

        $id = DB::table('subscribers')->insertGetId([
            'full_name'            => $data['full_name'],
            'email'                => $data['email'],
            'mobile_number'        => $data['mobile_number'] ?? null,
            'country_of_residency' => $data['country_of_residency'] ?? null,
            'nationality'          => $data['nationality'] ?? null,
            'referral_source'      => $data['referral_source'] ?? null,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return response()->json([
            'message' => 'Subscribed successfully',
            'id' => $id,
        ], 201);
    }
}
