<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionMealSubstituted extends Mailable
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

        return $this->view('email.customer.subscription-meal-substituted')
            ->with($this->data)
            ->subject('A Meal in Your Subscription Was Substituted')
            ->replyTo($storeEmail);
    }
}
