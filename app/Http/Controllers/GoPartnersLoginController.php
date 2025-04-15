<?php

namespace App\Http\Controllers;

use App\Models\GoPartnersLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class GoPartnersLoginController extends Controller
{
    public function delete(Request $request, $id)
    {
        try {
            $partner = GoPartnersLogin::find($id);

            if (!$partner) {
                return response()->json([
                    'message' => 'Partner not found'
                ], 404);
            }

            // Check registration process status
            if ($partner->status === 'pending') {
                return response()->json([
                    'message' => 'Cannot delete partner with pending registration. Please complete or cancel registration first.'
                ], 403);
            }

            // Delete document from BunnyCDN if it exists
            if ($partner->document_url) {
                $apiKey = env('BUNNYCDN_API_KEY');
                $storageZone = env('BUNNYCDN_STORAGE_ZONE');
                $hostname = env('BUNNYCDN_HOSTNAME');
                $filePath = $partner->document_url;
                $url = "https://{$hostname}/{$storageZone}/{$filePath}";

                $response = Http::withHeaders([
                    'AccessKey' => $apiKey,
                ])->delete($url);

                if ($response->status() !== 200) {
                    Log::warning('Failed to delete document from BunnyCDN', [
                        'partner_id' => $id,
                        'file' => $filePath,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    // Continue with deletion even if document removal fails
                    // You could alternatively return an error here if document deletion is critical
                }
            }

            // Delete the partner record
            $partner->delete();

            return response()->json([
                'message' => 'Partner and associated document deleted successfully',
                'partner_id' => $id
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting partner', [
                'partner_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error deleting partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAll(Request $request)
    {
        $partners = GoPartnersLogin::all();

        return response()->json([
            'message' => 'Partners retrieved successfully',
            'data' => $partners
        ], 200);
    }

    public function updateProfile(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:go_partners_logins,email,' . $id,
            'phone' => 'sometimes|string|max:255|unique:go_partners_logins,phone,' . $id,
            'password' => 'sometimes|string|min:8',
        ]);

        $partner = GoPartnersLogin::find($id);

        if (!$partner) {
            return response()->json([
                'message' => 'Partner not found',
            ], 404);
        }

        // Update fields only if they are provided
        if ($request->has('first_name')) {
            $partner->first_name = $request->first_name;
        }
        if ($request->has('last_name')) {
            $partner->last_name = $request->last_name;
        }
        if ($request->has('email')) {
            $partner->email = $request->email;
            $partner->email_verified = false; // Require re-verification if email changes
            Log::info('Email changed, setting email_verified to false', ['partner_id' => $id]);
        }
        if ($request->has('phone')) {
            $partner->phone = $request->phone;
        }
        if ($request->has('password')) {
            $newPassword = Hash::make($request->password);
            $partner->password = $newPassword;
            Log::info('Password updated', [
                'partner_id' => $id,
                'new_hashed_password' => $newPassword
            ]);
        }

        try {
            $partner->save();
            Log::info('Profile saved successfully', ['partner_id' => $id]);
        } catch (\Exception $e) {
            Log::error('Failed to save profile', [
                'partner_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Failed to update profile due to a server error',
                'error' => $e->getMessage()
            ], 500);
        }

        // Clear any existing tokens to force re-login with new credentials
        $partner->tokens()->delete();

        return response()->json([
            'message' => 'Profile updated successfully',
            'partner' => $partner,
        ], 200);
    }
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:go_partners_logins',
            'phone' => 'required|string|max:255|unique:go_partners_logins',
            'password' => 'required|string|min:8',
            'verify_password' => 'required|same:password',
        ]);

        $emailCode = Str::random(6);

        // Create temporary registration with status pending
        $partner = GoPartnersLogin::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'email_verification_code' => $emailCode,
            'status' => 'pending'
        ]);

        try {
            Mail::to($partner->email)->send(new \App\Mail\GoPartnersLoginVerification($partner));
        } catch (\Exception $e) {
            $partner->delete();
            return response()->json([
                'message' => 'Failed to send email verification: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Please verify your email to continue registration',
            'partner_id' => $partner->id,
        ], 201);
    }

    public function forgotPassword(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:go_partners_logins,email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'error' => 'Validation failed',
            'details' => $validator->errors()
        ], 400);
    }

    // Customize the reset URL to point to the public/reset-password.html file
    ResetPasswordNotification::createUrlUsing(function ($notifiable, $token) {
        return url('/Go-Partners-reset-password.html?token=' . $token . '&email=' . urlencode($notifiable->email));
    });

    // Send password reset link
    $status = Password::sendResetLink(
        $request->only('email')
    );

    // Handle different status responses
    switch ($status) {
        case Password::RESET_LINK_SENT:
            return response()->json([
                'status' => 200,
                'success' => 'Password reset link sent to your email'
            ], 200);
        case Password::RESET_THROTTLED:
            return response()->json([
                'status' => 429,
                'error' => 'Too many reset attempts. Please wait a minute and try again.'
            ], 429);
        default:
            return response()->json([
                'status' => 500,
                'error' => 'Failed to send reset link',
                'details' => $status
            ], 500);
    }
}
public function resetPassword(Request $request)
{
    // Validate request data
    $request->validate([
        'email' => 'required|email|exists:go_partners_logins,email', // Adjust table if needed
        'password' => 'required|min:6|confirmed',
        'token' => 'required'
    ]);

    // Retrieve the reset token entry from DB
    $reset = DB::table('password_resets')->where('email', $request->email)->first();

    // Check if token exists and matches
    if (!$reset || !Hash::check($request->token, $reset->token)) {
        return response()->json(['error' => 'Invalid or expired token'], 400);
    }

    // Find the user/partner and update password
    $partner = \App\Models\GoPartnersLogin::where('email', $request->email)->first();
    if (!$partner) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Hash and update password
    $partner->password = Hash::make($request->password);
    try {
        $partner->save();

        // Log success
        Log::info('Password reset successful', [
            'email' => $partner->email,
            'partner_id' => $partner->id
        ]);

        // Delete the password reset record
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Delete user tokens (logout all sessions)
        $partner->tokens()->delete();

        return response()->json(['success' => 'Password reset successfully'], 200);
    } catch (\Exception $e) {
        Log::error('Failed to save reset password', [
            'email' => $partner->email,
            'error' => $e->getMessage(),
            'partner_id' => $partner->id
        ]);
        return response()->json(['error' => 'Failed to reset password'], 500);
    }
}


    public function verifyEmail(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:go_partners_logins,id',
            'code' => 'required|string',
        ]);

        $partner = GoPartnersLogin::find($request->partner_id);

        if ($partner->email_verification_code === $request->code) {
            $partner->email_verified = true;
            $partner->email_verification_code = null;
            $partner->status = 'email_verified'; // Update status
            $partner->save();

            return response()->json([
                'message' => 'Email verified successfully. Please upload your document to complete registration',
                'partner_id' => $partner->id,
            ]);
        }

        return response()->json([
            'message' => 'Invalid email verification code',
        ], 400);
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:go_partners_logins,id',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $partner = GoPartnersLogin::find($request->partner_id);

        if (!$partner) {
            return response()->json(['message' => 'Partner not found'], 404);
        }

        if (!$partner->email_verified || $partner->status !== 'email_verified') {
            return response()->json(['message' => 'Please verify your email first'], 403);
        }

        try {
            $file = $request->file('file');

            Log::info('Document upload request', [
                'partner_id' => $partner->id,
                'file_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);

            $apiKey = env('BUNNYCDN_API_KEY');
            $storageZone = env('BUNNYCDN_STORAGE_ZONE');
            $hostname = env('BUNNYCDN_HOSTNAME');
            $pullZone = filter_var(env('BUNNYCDN_PULL_ZONE'), FILTER_SANITIZE_URL) ?: 'https://default.b-cdn.net';

            $originalName = preg_replace('/[^A-Za-z0-9\-\.]/', '', $file->getClientOriginalName());
            $fileName = 'Documents/' . $partner->id . '-' . time() . '-' . $originalName;
            $url = "https://{$hostname}/{$storageZone}/{$fileName}";

            $resource = fopen($file->getRealPath(), 'r');
            $stream = new Stream($resource);

            $response = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Content-Type' => 'application/pdf',
            ])
            ->withOptions(['allow_redirects' => false])
            ->withBody($stream, 'application/pdf')
            ->put($url);

            fclose($resource);

            if ($response->status() !== 201) {
                Log::error('BunnyCDN upload failed', [
                    'file' => $fileName,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to upload document'], 500);
            }

            // Complete registration after successful document upload
            $partner->document_url = $fileName;
            $partner->status = 'active';
            $partner->save();

            $token = $partner->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registration completed successfully',
                'public_url' => rtrim($pullZone, '/') . '/' . $fileName,
                'partner' => $partner,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Document upload error', [
                'partner_id' => $partner->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $partner = GoPartnersLogin::where('email', $request->email)->first();

        if (!$partner) {
            Log::warning('Login failed: Partner not found', ['email' => $request->email]);
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!Hash::check($request->password, $partner->password)) {
            Log::warning('Login failed: Password mismatch', [
                'email' => $request->email,
                'provided_password' => $request->password, // Be cautious logging this in production
                'stored_hash' => $partner->password
            ]);
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$partner->email_verified) {
            Log::warning('Login failed: Email not verified', ['email' => $request->email]);
            return response()->json([
                'message' => 'Please verify your email first',
            ], 403);
        }

        // Generate Sanctum token
        $token = $partner->createToken('auth_token')->plainTextToken;

        Log::info('Login successful', ['email' => $request->email, 'partner_id' => $partner->id]);

        return response()->json([
            'message' => 'Login successful',
            'partner' => $partner,
            'token' => $token,
        ], 200);
    }
    // code with Phone auth
    // public function register(Request $request)
    // {
    //     $validated = $request->validate([
    //         'first_name' => 'required|string|max:255',
    //         'last_name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:go_partners_logins',
    //         'phone' => 'required|string|max:255|unique:go_partners_logins',
    //         'password' => 'required|string|min:8',
    //         'verify_password' => 'required|same:password',
    //     ]);

    //     $emailCode = Str::random(6);
    //     $phoneCode = Str::random(6);

    //     $partner = GoPartnersLogin::create([
    //         'first_name' => $request->first_name,
    //         'last_name' => $request->last_name,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'password' => Hash::make($request->password),
    //         'email_verification_code' => $emailCode,
    //         'phone_verification_code' => $phoneCode,
    //     ]);

    //     try {
    //         // Send email verification
    //         Mail::to($partner->email)->send(new \App\Mail\GoPartnersLoginVerification($partner));
    //     } catch (\Exception $e) {
    //         $partner->delete();
    //         return response()->json([
    //             'message' => 'Failed to send email verification: ' . $e->getMessage(),
    //         ], 500);
    //     }

    //     try {
    //         // Send phone verification
    //         $this->sendPhoneVerification($partner->phone, $phoneCode);
    //     } catch (RestException $e) {
    //         $partner->delete();
    //         return response()->json([
    //             'message' => 'Failed to send phone verification: ' . $e->getMessage(),
    //             'twilio_error_code' => $e->getCode(), // Twilio-specific error code
    //         ], 500);
    //     } catch (\Exception $e) {
    //         $partner->delete();
    //         return response()->json([
    //             'message' => 'Unexpected error during phone verification: ' . $e->getMessage(),
    //         ], 500);
    //     }

    //     return response()->json([
    //         'message' => 'Registration successful. Please check your email and phone for verification codes.',
    //         'partner_id' => $partner->id,
    //     ], 201);
    // }

    // public function verifyEmail(Request $request)
    // {
    //     $request->validate([
    //         'partner_id' => 'required|exists:go_partners_logins,id',
    //         'code' => 'required|string',
    //     ]);

    //     $partner = GoPartnersLogin::find($request->partner_id);

    //     if ($partner->email_verification_code === $request->code) {
    //         $partner->email_verified = true;
    //         $partner->email_verification_code = null;
    //         $partner->save();

    //         return response()->json([
    //             'message' => 'Email verified successfully',
    //         ]);
    //     }

    //     return response()->json([
    //         'message' => 'Invalid email verification code',
    //     ], 400);
    // }

    // public function verifyPhone(Request $request)
    // {
    //     $request->validate([
    //         'partner_id' => 'required|exists:go_partners_logins,id',
    //         'code' => 'required|string',
    //     ]);

    //     $partner = GoPartnersLogin::find($request->partner_id);

    //     if ($partner->phone_verification_code === $request->code) {
    //         $partner->phone_verified = true;
    //         $partner->phone_verification_code = null;
    //         $partner->save();

    //         return response()->json([
    //             'message' => 'Phone verified successfully',
    //         ]);
    //     }

    //     return response()->json([
    //         'message' => 'Invalid phone verification code',
    //     ], 400);
    // }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     $partner = GoPartnersLogin::where('email', $request->email)->first();

    //     if (!$partner || !Hash::check($request->password, $partner->password)) {
    //         return response()->json([
    //             'message' => 'Invalid credentials',
    //         ], 401);
    //     }

    //     if (!$partner->email_verified || !$partner->phone_verified) {
    //         return response()->json([
    //             'message' => 'Please verify both your email and phone first',
    //         ], 403);
    //     }

    //     return response()->json([
    //         'message' => 'Login successful',
    //         'partner' => $partner,
    //     ]);
    // }

    // protected function sendPhoneVerification($phone, $code)
    // {
    //     $twilio_sid = env('TWILIO_SID');
    //     $twilio_token = env('TWILIO_AUTH_TOKEN');
    //     $twilio_number = env('TWILIO_PHONE_NUMBER');

    //     if (!$twilio_sid || !$twilio_token || !$twilio_number) {
    //         throw new \Exception('Twilio configuration is incomplete. Check your .env file.');
    //     }

    //     $client = new Client($twilio_sid, $twilio_token);

    //     $client->messages->create(
    //         $phone,
    //         [
    //             'from' => $twilio_number,
    //             'body' => "Your GoPartners verification code is: {$code}",
    //         ]
    //     );
    // }
}