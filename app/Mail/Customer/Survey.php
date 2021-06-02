<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\StoreDetail;
use App\Store;

class Survey extends Mailable
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
        $storeId = $this->data['order']->store_id;
        $store = Store::where('id', $storeId)->first();
        $storeEmail = $store->user->email;

        $storeDetails = StoreDetail::where('store_id', $storeId)->first();

        $storeName = $storeDetails->name;

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

        return $this->view('email.customer.survey')
            ->with($this->data)
            ->subject('Did you enjoy your order from ' . $storeName . '?')
            ->from($storeEmail, $storeName);
    }
}
