<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customer;
use Illuminate\Support\Carbon;

class UpdateCustomersTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:updateCustomersTable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring customer attributes to fields to reduce load time.';

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
        $customers = Customer::all();

        foreach ($customers as $customer) {
            try {
                // $customer->email = $customer->user->email;
                // $customer->name = $customer->user->name;
                // $customer->firstname = $customer->user->userDetail->firstname;
                // $customer->lastname = $customer->user->userDetail->lastname;
                // $customer->phone = $customer->user->userDetail->phone;
                // $customer->address = $customer->user->userDetail->address;
                // $customer->city = $customer->user->userDetail->city;
                // $customer->state = $customer->user->userDetail->state;
                // $customer->zip = $customer->user->userDetail->zip;
                // $customer->delivery = $customer->user->userDetail->delivery;

                // $customer->last_order = $customer->user->order
                //     ->where('store_id', $customer->store_id)
                //     ->max("created_at");
                $this->info($customer->id);

                $customer->total_payments = $customer->user->order
                    ->where('store_id', $customer->store_id)
                    ->where('paid', 1)
                    ->count();

                // $customer->total_paid = $customer->user->order
                //     ->where('store_id', $customer->store_id)
                //     ->where('paid', 1)
                //     ->sum("amount");

                $customer->update();
            } catch (\Exception $e) {
            }
        }
    }
}
