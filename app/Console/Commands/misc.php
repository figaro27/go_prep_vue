<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Store;
use App\Customer;
use App\User;
use App\UserDetail;
use Illuminate\Support\Carbon;
use App\PurchasedGiftCard;

class misc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:misc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Miscellaneous scripts. Intentionally left blank. Code changed / added directly on server when needed.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $purchasedGiftCards = PurchasedGiftCard::all();
        foreach ($purchasedGiftCards as $purchasedGiftCard) {
            try {
                if ($purchasedGiftCard->order->user) {
                    $purchasedGiftCard->purchased_by =
                        $purchasedGiftCard->order->user->user_role_id === 1
                            ? $purchasedGiftCard->order->user->details
                                    ->firstname .
                                ' ' .
                                $purchasedGiftCard->order->user->details
                                    ->lastname
                            : $purchasedGiftCard->store->details->name;
                    $purchasedGiftCard->update();
                }
            } catch (\Exception $e) {
            }
        }
    }
}
