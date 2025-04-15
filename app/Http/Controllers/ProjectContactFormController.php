<?php

namespace App\Http\Controllers;

use App\Models\ProjectContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectContactFormController extends Controller
{
    public function index()
    {
        return response()->json(ProjectContact::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'     => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'email'         => 'required|email',
            'message'       => 'required|string',
            'property_id'   => 'required|integer',
            'property_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact = ProjectContact::create($request->all());

        return response()->json(['message' => 'Contact submitted successfully', 'data' => $contact], 201);
    }

    public function show($id)
    {
        $contact = ProjectContact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        return response()->json($contact);
    }

    public function update(Request $request, $id)
    {
        $contact = ProjectContact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->update($request->all());

        return response()->json(['message' => 'Contact updated successfully', 'data' => $contact]);
    }

    public function destroy($id)
    {
        $contact = ProjectContact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }
}