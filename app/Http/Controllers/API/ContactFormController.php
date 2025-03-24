<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ContactForm;
use Illuminate\Http\Request;

class ContactFormController extends Controller
{
    // Get all contact form submissions
    public function index()
    {
        $contactForms = ContactForm::all();
        return response()->json($contactForms, 200);
    }

    // Submit a new contact form
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'nullable|string',
        ]);

        $contactForm = ContactForm::create($request->only(
            'full_name', 'phone', 'email', 'message'
        ));

        return response()->json($contactForm, 201);
    }
}
