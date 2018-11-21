<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\StoreContactEmail;
use Mail;
use App\Store;
use App\StoreDetail;
use App\UserDetail;
use Auth;

class ContactFormController extends Controller
{
    public function submitStore(Request $request) {
        $this->validate($request, [
            'name' => 'required|string',
            'message' => 'required',
        ]);

        $email = Auth::user()->email;
        $id = Auth::user()->id;
  		$storeID = Store::where('user_id', $id)->pluck('id')->first();
  		$storeName = StoreDetail::where('store_id', $storeID)->pluck('name')->first();

  		$data = array(
        	'name' => $request->name,
        	'email' => $email,
        	'storeID' => $storeID,
        	'storeName' => $storeName,
        	'body' => $request->message
        );

  		Mail::send('email.contact-store', $data, function($message) use ($data){
        	$message->from($data['email']);
        	$message->to('admin@goprep.com');
        });
    }


    public function submitCustomer(Request $request) {
        $this->validate($request, [
            'message' => 'required',
        ]);

        $email = Auth::user()->email;
        $id = Auth::user()->id;
  		$firstname = UserDetail::where('user_id', $id)->pluck('firstname')->first();
  		$lastname = UserDetail::where('user_id', $id)->pluck('lastname')->first();

  		$data = array(
        	'firstname' => $firstname,
        	'lastname' => $lastname,
        	'email' => $email,
        	'body' => $request->message
        );

  		Mail::send('email.contact-customer', $data, function($message) use ($data){
        	$message->from($data['email']);
        	$message->to('admin@goprep.com');
        });
    }
}
