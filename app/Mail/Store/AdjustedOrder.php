<?php

namespace App\Mail\Store;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdjustedOrder extends Mailable
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
        $emailBranding = $order->store->modules->emailBranding;
        $storeEmail = $order->store->user->email;
        $storeName = $order->store->details->name;

        return $this->view('email.store.adjusted-order')
            ->with($this->data)
            ->subject('Your Order Was Adjusted')
            ->from($storeEmail, $storeName);
    }
}
