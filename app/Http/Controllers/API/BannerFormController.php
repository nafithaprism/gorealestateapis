<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BannerForm;
use Illuminate\Http\Request;

class BannerFormController extends Controller
{
    // Get all banner form submissions
    public function index()
    {
        $bannerForms = BannerForm::all();
        return response()->json($bannerForms, 200);
    }

    // Submit a new banner form
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'purchase_objective' => 'required|string|max:255',
            'min_budget' => 'required|numeric',
            'max_budget' => 'required|numeric|gte:min_budget',
            'message' => 'nullable|string',
        ]);

        $bannerForm = BannerForm::create($request->only(
            'first_name', 'last_name', 'company', 'phone', 'email',
            'purchase_objective', 'min_budget', 'max_budget', 'message'
        ));

        return response()->json($bannerForm, 201);
    }
}