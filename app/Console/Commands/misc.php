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
        $mealSizes = MealSize::withTrashed()->get();

        foreach ($mealSizes as $mealSize) {
            try {
                $storeId = Meal::where('id', $mealSize->meal_id)
                    ->withTrashed()
                    ->pluck('store_id')
                    ->first();
                $mealSize->store_id = $storeId;
                $mealSize->update();
            } catch (\Exception $e) {
                $this->info($mealSize);
            }
        }
    }
}
