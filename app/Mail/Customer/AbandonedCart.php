<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AbandonedCart extends Mailable
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
        $storeEmail = $this->data['store_email'];
        $storeName = $this->data['store_name'];

        return $this->view('email.customer.abandoned-cart')
            ->with($this->data)
            ->subject('Don\'t Forget Your Bag!')
            ->from($storeEmail, $storeName);
    }
}
