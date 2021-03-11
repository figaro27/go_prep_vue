<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customer;
use App\User;

class mergeCustomerOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:mergeCustomerOrderTotals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If a customer placed a regular order and a cash order, they are saved as two separate customer entries. The first entry is the only one presented on the Store/Customers page. Code was changed to merge the orders from both customers. This command now merges all the totals in the customers table like Total Payments and Total Spent from both customer types.';

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

        $customers = $customers->groupBy('user_id');

        foreach ($customers as $customer) {
            $this->info($customer->id);
            try {
                $groupedCustomers = json_decode($customer);
                if (count($groupedCustomers) > 1) {
                    $firstCustomer = Customer::where(
                        'id',
                        $groupedCustomers[0]->id
                    )->first();
                    foreach ($groupedCustomers as $i => $groupedCustomer) {
                        if ($i > 0) {
                            $firstCustomer->total_payments +=
                                $groupedCustomer->total_payments;
                            $firstCustomer->total_paid +=
                                $groupedCustomer->total_paid;
                            if (
                                $groupedCustomer->last_order >
                                $firstCustomer->last_order
                            ) {
                                $firstCustomer->last_order =
                                    $groupedCustomer->last_order;
                            }
                            $firstCustomer->update();
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->info($e);
            }
        }
    }
}
