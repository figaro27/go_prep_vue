<?php

namespace App\Console\Commands;

use App\Card;
use Illuminate\Console\Command;

class PopulateCardStoreIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate_card_store_ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        foreach (Card::all() as $card) {
            if (!$card->user) {
                continue;
            }
            $storeId =
                $card->user
                    ->customers()
                    ->where('payment_gateway', $card->payment_gateway)
                    ->first()->store_id ?? null;
            if (!$storeId) {
                continue;
            }

            $card->store_id = $storeId;
            $card->save();
        }
    }
}
