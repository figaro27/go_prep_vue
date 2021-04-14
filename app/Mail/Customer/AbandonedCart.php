<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\StoreDetail;

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

        $storeDetails = StoreDetail::where(
            'store_id',
            $this->data['store_id']
        )->first();

        $logo = $storeDetails->getMedia('logo')->first();

        if ($logo) {
            $path = $logo->getPath('thumb');

            if (file_exists($path)) {
                $logo_b64 = \App\Utils\Images::encodeB64($path);

                if ($logo_b64) {
                    $this->data['logo_b64'] = $logo_b64;
                }
            }
        }

        return $this->view('email.customer.abandoned-cart')
            ->with($this->data)
            ->subject('Don\'t Forget Your Bag!')
            ->from($storeEmail, $storeName);
    }
}
