<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\StoreSetting;

class SetStatementDescriptors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:setStatementDescriptors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets all the current store\'s statement descriptors from Stripe into the store_settings table.';

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
        $storeSettings = StoreSetting::all();

        foreach ($storeSettings as $storeSetting) {
            try {
                \Stripe\Stripe::setApiKey(
                    $storeSetting->stripe_account['access_token']
                );
                $payments = \Stripe\Account::retrieve(
                    $storeSetting->stripe_id,
                    [
                        'settings' => [
                            'payments' => ['statement_descriptor']
                        ]
                    ]
                );
                $storeSetting->statementDescriptor =
                    $payments['statement_descriptor'];
                $storeSetting->update();
            } catch (\Exception $e) {
            }
        }
    }
}
