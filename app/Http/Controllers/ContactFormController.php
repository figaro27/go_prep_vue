<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\StoreContactEmail;

class ContactFormController extends Controller
{
    public function submit(Request $request) {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required',
        ]);
        
        Mail::to(config('mail.support.address'))->send(new StoreContactEmail($contact));

        return response()->json(null, 200);
    }
}
