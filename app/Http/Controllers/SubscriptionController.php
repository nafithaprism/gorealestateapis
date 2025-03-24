<?php

// app/Http/Controllers/SubscriptionController.php

namespace App\Http\Controllers;

use App\Mail\SubscriptionConfirmation;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    // Create
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:subscribers,email',
        ]);

        try {
            $subscription = Subscription::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'is_subscribed' => 1, // Changed from is_active to match your schema
                'subscribed_at' => now()
            ]);

            Mail::to($validated['email'])
                ->send(new SubscriptionConfirmation($validated['full_name']));

            return response()->json([
                'message' => 'Subscription successful',
                'data' => $subscription
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process subscription',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Read All
    public function index()
    {
        $subscriptions = Subscription::all();

        return response()->json([
            'message' => 'Subscriptions retrieved successfully',
            'data' => $subscriptions
        ], 200);
    }

    // Read Single
    public function show($id)
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Subscription retrieved successfully',
            'data' => $subscription
        ], 200);
    }

    // Update
    public function update(Request $request, $id)
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:subscriptions,email,' . $id,
            'is_subscribed' => 'sometimes|boolean' // Changed from is_active
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscription->update($request->only(['full_name', 'email', 'is_subscribed'])); // Updated field name

            return response()->json([
                'message' => 'Subscription updated successfully',
                'data' => $subscription
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update subscription',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Delete
    public function destroy($id)
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        try {
            $subscription->delete();

            return response()->json([
                'message' => 'Subscription deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete subscription',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Check subscription status
    public function checkSubscription(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        $subscription = Subscription::where('email', $validated['email'])->first();

        return response()->json([
            'email' => $validated['email'],
            'isSubscribed' => $subscription ? (bool)$subscription->is_subscribed : false, // Updated field name
            'subscriptionDate' => $subscription?->subscribed_at
        ], 200);
    }
}