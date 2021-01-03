<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class RenewalFailed extends Mailable
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
        $storeName = $this->data['store']['store_detail']['name'];
        $storeEmail = User::where('id', $this->data['store']['user_id'])
            ->pluck('email')
            ->first();

        return $this->view('email.customer.renewal-failed')
            ->with($this->data)
            ->from($storeEmail, $storeName);
    }
}
