<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
use App\StoreDetail;

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

        $storeDetails = StoreDetail::where(
            'store_id',
            $this->data['store']['id']
        )->first();

        $logo = $storeDetails->getMedia('logo')->first();

        if ($logo) {
            $path = $logo->getPath('thumb');

            if (file_exists($path)) {
                $logo_b64 = \App\Utils\Images::encodeB64($path);

                if ($logo_b64) {
                    $data['logo_b64'] = $logo_b64;
                }
            }
        }

        return $this->view('email.customer.renewal-failed')
            ->with($this->data)
            ->from($storeEmail, $storeName);
    }
}
