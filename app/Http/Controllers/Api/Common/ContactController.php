<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $data = $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'required|email',
            'company' => 'nullable|string',
            'message' => 'required|string',
        ]);
        $contact = Contact::create($data);
        return $this->successResponse($contact, 'Contact form submitted successfully', 201);
    }
}
