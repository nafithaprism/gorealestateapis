<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    // Get all FAQs
    public function index()
    {
        $faqs = Faq::all();
        return response()->json($faqs, 200);
    }

    // Get a single FAQ by id
    public function show($id)
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return response()->json(['error' => 'FAQ not found'], 404);
        }
        return response()->json($faq, 200);
    }

    // Add a new FAQ
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq = Faq::create($request->only('question', 'answer'));
        return response()->json($faq, 201);
    }

    // Update an existing FAQ by id
    public function update(Request $request, $id)
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return response()->json(['error' => 'FAQ not found'], 404);
        }

        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq->update($request->only('question', 'answer'));
        return response()->json($faq, 200);
    }

    // Delete an FAQ by id
    public function destroy($id)
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return response()->json(['error' => 'FAQ not found'], 404);
        }

        $faq->delete();
        return response()->json(null, 204);
    }
}
