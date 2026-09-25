<?php

namespace App\Http\Controllers;

use App\Services\AeriaMailService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(
        Request $request,
        AeriaMailService $mailService
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $mailService->sendContactMessage(
            name: $data['name'],
            email: $data['email'],
            message: $data['message']
        );

        return back()->with(
            'success',
            'Tu mensaje fue enviado correctamente. Te responderemos a la brevedad.'
        );
    }
}