<?php

namespace App\Mail\Store;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StorePlanCancelled extends Mailable
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
        return $this->view('email.store.store_plan_cancelled')
            ->with($this->data)
            ->to('danny@goprep.com')
            ->cc('mike@goprep.com')
            ->subject('Store Plan Cancelled');
    }
}
