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
        $orders = Order::all();

        foreach ($orders as $order) {
            try {
                $order->customer_company = $order->user->details->companyname;
                $order->customer_phone = $order->user->details->phone;
                $order->customer_city = $order->user->details->city;
                $order->customer_state = $order->user->details->state;
                $order->customer_delivery = $order->user->details->delivery;
                $order->update();
                $this->info($order->id . ' updated successfully.');
            } catch (\Exception $e) {
                $this->info('Error with ' . $order->id);
                $this->info($e);
            }
        }
    }
}
