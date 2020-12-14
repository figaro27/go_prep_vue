<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewGiftCard extends Mailable
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
        $giftCard = $this->data['purchasedGiftCard'];
        $order = $this->data['order'];
        $store = isset($this->data['store']) ? $this->data['store'] : null;
        $emailBranding = $store
            ? $store->modules->emailBranding
            : $order->store->modules->emailBranding;
        $storeEmail = $store ? $store->user->email : $order->store->user->email;
        $storeName = $store
            ? $store->details->name
            : $order->store->details->name;

        if ($emailBranding) {
            return $this->view('email.customer.new-gift-card')
                ->with($this->data)
                ->subject('New Gift Card')
                ->from($storeEmail, $storeName);
        } else {
            return $this->view('email.customer.new-gift-card')
                ->with($this->data)
                ->subject('New Gift Card');
        }
    }
}
