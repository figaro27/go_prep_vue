<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Store;
use App\Customer;
use App\User;
use App\UserDetail;
use Illuminate\Support\Carbon;
use App\PurchasedGiftCard;
use App\StoreSetting;
use App\MealMealTag;
use App\Meal;
use App\MealSize;
use App\Order;
use App\MealAttachment;
use App\MealMealPackageComponentOption;
use App\MealPackageComponentOption;
use App\MealPackageComponent;
use App\MealMealPackageAddon;
use App\MealPackageAddon;
use App\MealPackage;
use App\Subscription;
use App\StorePlan;
use App\StorePlanTransaction;
use App\PackingSlipSetting;
use App\Facades\StorePlanService;
use App\ChildMeal;
use App\ChildMealPackage;
use App\ChildGiftCard;
use App\GiftCard;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use App\Media\Utils as MediaUtils;

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
        // $customers = Customer::all();

        // foreach ($customers as $customer){
        //     try {
        //     $customer->company = $customer->user->details->companyname;
        //     $customer->update();
        //         $this->info($customer->id . 'customer updated successfully.');
        //     } catch (\Exception $e) {
        //         $this->info('Error with ' . $customer->id);
        //         $this->info($e);
        //     }
        // }

        $orders = Order::all();

        foreach ($orders as $order) {
            try {
                $order->customer_firstname = $order->user->details->firstname;
                $order->customer_lastname = $order->user->details->lastname;
                $order->customer_email = $order->user->email;
                $order->customer_company = $order->user->details->companyname;
                $order->update();
                $this->info($order->id . ' updated successfully.');
            } catch (\Exception $e) {
                $this->info('Error with ' . $order->id);
                $this->info($e);
            }
        }
    }
}
