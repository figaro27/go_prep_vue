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
        $plans = StorePlanService::getRenewingPlans();
        // $this->info(count($plans) . ' store plans renewing today');

        foreach ($plans as $plan) {
            if ($plan->method === 'connect' && $plan->amount > 0) {
                $this->info($plan->store_name);
                // dispatch(function () use ($plan) {
                //     StorePlanService::renew($plan);
                // });
            }
        }
    }
}
