<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionCancelled extends Mailable
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
        $subscription = $this->data['subscription'];
        $storeEmail = $subscription->store->user->email;
        $storeName = $subscription->store->details->name;
        $emailBranding = $subscription->store->modules->emailBranding;

        if ($emailBranding) {
            return $this->view('email.customer.cancelled-subscription')
                ->with($this->data)
                ->subject('Your Subscription Was Cancelled')
                ->from($storeEmail, $storeName);
        } else {
            return $this->view('email.customer.cancelled-subscription')
                ->with($this->data)
                ->subject('Your Subscription Was Cancelled');
        }
    }
}
