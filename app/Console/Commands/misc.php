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
        $mealMealTags = MealMealTag::findMany([
            346,
            367,
            447,
            446,
            476,
            714,
            713,
            828,
            836,
            846,
            936,
            954,
            124,
            988,
            1161,
            1207,
            1425,
            1429,
            1980,
            1996,
            2003,
            1982,
            2426,
            2490,
            1984,
            3042,
            3060,
            2720,
            2864,
            2829,
            2704,
            2877,
            2667,
            2895,
            2977,
            2544,
            3421,
            3499,
            2547,
            3650,
            3101,
            2762,
            4209,
            4284,
            4285,
            4286,
            4287,
            4448,
            4474,
            4475,
            4496,
            4495,
            5781,
            6454,
            6520,
            7124,
            7734,
            10685,
            11007,
            10995,
            9505
        ]);

        foreach ($mealMealTags as $mealMealTag) {
            try {
                $mealMealTag->delete();
            } catch (\Exception $e) {
            }
        }
    }
}
