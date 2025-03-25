<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'error' => 'Validation failed', 'details' => $validator->errors()], 400);
        }

        // Retrieve user by email and user_type
        $user = User::where('email', $request->email)
                    ->where('user_type', $request->user_type)
                    ->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 401, 'error' => 'Credentials do not match'], 401);
        }

        // Create a new access token
        $tokenResult = $user->createToken('API_Token');
        $token = $tokenResult->accessToken;

        // Ensure token is not null
        if (!$token) {
            return response()->json(['status' => 500, 'error' => 'Failed to generate token'], 500);
        }

        // Return response with user details
        return response()->json([
            'status' => 200,
            'success' => 'Logged in successfully',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name ?? 'Admin', // Use actual field if available
                'last_name' => $user->last_name ?? 'admin',
                'email' => $user->email,
                'mobile' => $user->mobile ?? '0555532701',
                'email_verified_at' => $user->email_verified_at,
                'user_type' => $user->user_type,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ],
            'token' => $token
        ], 200)->header('x_auth_token', $token)
        ->header('Access-Control-Expose-Headers', 'x_auth_token');
    }
    public function registerUser(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'user_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'error' => 'Validation failed', 'details' => $validator->errors()], 400);
        }

        // Create new user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        // Create a new access token
        $tokenResult = $user->createToken('API_Token');
        $token = $tokenResult->accessToken;

        // Return response with user details
        return response()->json([
            'status' => 201,
            'success' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ],
            'token' => $token
        ], 201)->header('x_auth_token', $token)
          ->header('access-control-expose-headers', 'x_auth_token');
    }
    public function deleteUser(Request $request, $id)
    {
        // Validate request (e.g., ensure ID is provided and exists)
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'error' => 'Validation failed', 'details' => $validator->errors()], 400);
        }

        // Find the user by ID
        $user = User::find($id);

        // Optional: Add authorization check (e.g., only allow admins or the user themselves to delete)
        if (Auth::user()->user_type !== 'admin' && Auth::user()->id !== $user->id) {
            return response()->json(['status' => 403, 'error' => 'Unauthorized action'], 403);
        }

        // Delete the user
        $user->delete();

        // Return success response
        return response()->json([
            'status' => 200,
            'success' => 'User deleted successfully'
        ], 200);
    }
    public function getAllUsers(Request $request)
{
    // Optional: Add authorization check (e.g., only admins can access this)
    if (Auth::user()->user_type !== 'admin') {
        return response()->json(['status' => 403, 'error' => 'Unauthorized action'], 403);
    }

    // Retrieve all users
    $users = User::all();

    // Check if there are any users
    if ($users->isEmpty()) {
        return response()->json(['status' => 404, 'error' => 'No users found'], 404);
    }

    // Format the user data
    $userData = $users->map(function ($user) {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'first_name' => $user->first_name ?? 'N/A', // Assuming optional fields, adjust as needed
            'last_name' => $user->last_name ?? 'N/A',
            'mobile' => $user->mobile ?? 'N/A',
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];
    });

    // Return success response with all users
    return response()->json([
        'status' => 200,
        'success' => 'Users retrieved successfully',
        'users' => $userData
    ], 200);
}
}