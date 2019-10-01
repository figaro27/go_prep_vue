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
        $store = Store::where('id', $lastViewedStoreId)->first();
        $lastViewedStore = $store->details->name;
        $storeEmail = $store->user->email;
        // $lastViewedStore = StoreDetail::where('id', $lastViewedStoreId)
        //     ->pluck('name')
        //     ->first();
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
            'lastViewedStore' => $lastViewedStore,
            'storeEmail' => $storeEmail
        );

        Mail::send('email.contact-customer', $data, function ($message) use (
            $data
        ) {
            $message->from($data['email']);
            $message
                ->to($data['storeEmail'])
                ->bcc('mike@goprep.com')
                ->subject('GoPrep - Contact Form Inquiry from Customer');
        });
    }
}
