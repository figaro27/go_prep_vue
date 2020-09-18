<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionUpdated extends Mailable
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
        $reason = $this->data['reason'];
        $meal = $this->data['meal'];
        $storeEmail = $subscription->store->user->email;
        $storeName = $subscription->store->details->name;

        return $this->view('email.customer.subscription-updated')
            ->with($this->data)
            ->subject('Subscription Updated')
            ->from($storeEmail, $storeName);
    }
}
