<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BannerForm;
use Illuminate\Http\Request;

class BannerFormController extends Controller
{
    // GET /api/banner-forms
    public function index()
    {
        return response()->json(BannerForm::all(), 200);
    }

    // POST /api/banner-forms
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'purchase_objective' => 'required|string|in:buy to live,invest to flip {sale},invest to live {short term / holiday concept}',
            'purchase_primary_goal' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric',
            'message' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $bannerForm = BannerForm::create($request->all());

        return response()->json($bannerForm, 201);
    }

    // GET /api/banner-forms/{id}
    public function show($id)
    {
        $bannerForm = BannerForm::find($id);

        if (!$bannerForm) {
            return response()->json(['message' => 'Form not found'], 404);
        }

        return response()->json($bannerForm, 200);
    }

    // PUT /api/banner-forms/{id}
    public function update(Request $request, $id)
    {
        $bannerForm = BannerForm::find($id);

        if (!$bannerForm) {
            return response()->json(['message' => 'Form not found'], 404);
        }

        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'number' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|max:255',
            'purchase_objective' => 'sometimes|required|string|in:buy to live,invest to flip {sale},invest to live {short term / holiday concept}',
            'purchase_primary_goal' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric',
            'message' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $bannerForm->update($request->all());

        return response()->json($bannerForm, 200);
    }

    // DELETE /api/banner-forms/{id}
    public function destroy($id)
    {
        $bannerForm = BannerForm::find($id);

        if (!$bannerForm) {
            return response()->json(['message' => 'Form not found'], 404);
        }

        $bannerForm->delete();

        return response()->json(['message' => 'Form deleted'], 200);
    }
}
