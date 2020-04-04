<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewReferral extends Mailable
{
    use Queueable, SerializesModels;

    protected $data = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = $this->data['order'];
        $referral = $this->data['referral'];
        $referralAmount = $this->data['referralAmount'];
        $emailBranding = $order->store->modules->emailBranding;
        $storeEmail = $order->store->user->email;
        $storeName = $order->store->details->name;

        if ($emailBranding) {
            return $this->view('email.customer.new-referral')
                ->with($this->data)
                ->subject('New Referral')
                ->from($storeEmail, $storeName);
        } else {
            return $this->view('email.customer.new-referral')
                ->with($this->data)
                ->subject('New Referral');
        }
    }
}
