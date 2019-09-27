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
    public function submitStore(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'subject' => 'required'
        ]);

        $email = auth('api')->user()->email;
        $id = auth('api')->user()->id;
        $storeID = Store::where('user_id', $id)
            ->pluck('id')
            ->first();
        $storeName = StoreDetail::where('store_id', $storeID)
            ->pluck('name')
            ->first();

        $data = array(
            'email' => $email,
            'storeID' => $storeID,
            'storeName' => $storeName,
            'subject' => $request->subject,
            'body' => $request->message
        );

        Mail::send('email.contact-store', $data, function ($message) use (
            $data
        ) {
            $message->from($data['email']);
            $message->to('support@goprep.com');
        });
    }

    public function submitCustomer(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'subject' => 'required'
        ]);

        $email = auth('api')->user()->email;
        $id = auth('api')->user()->id;
        $lastViewedStoreId = auth('api')->user()->last_viewed_store_id;
        $lastViewedStore = StoreDetail::where(
            'id',
            $lastViewedStoreId
        )->first();
        $firstname = UserDetail::where('user_id', $id)
            ->pluck('firstname')
            ->first();
        $lastname = UserDetail::where('user_id', $id)
            ->pluck('lastname')
            ->first();

        $data = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'customerID' => $id,
            'email' => $email,
            'subject' => $request->subject,
            'body' => $request->message,
            'lastViewedStore' => $lastViewedStore
        );

        Mail::send('email.contact-customer', $data, function ($message) use (
            $data
        ) {
            $message->from($data['email']);
            $message->to('support@goprep.com');
        });
    }
}
