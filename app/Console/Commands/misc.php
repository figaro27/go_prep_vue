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
        $meals = Meal::where('store_id', 40)->get();

        foreach ($meals as $meal) {
            $newMeal = Meal::where([
                'store_id' => 313,
                'title' => $meal->title,
                'description' => $meal->description,
                'price' => $meal->price,
                'deleted_at' => null
            ])->first();

            $mediaItem = $meal->getMedia('featured_image')->first();
            if ($mediaItem && $newMeal) {
                $this->info($newMeal->title);
                try {
                    $copiedMediaItem = $mediaItem->copy(
                        $newMeal,
                        'featured_image',
                        's3'
                    );
                } catch (\Exception $e) {
                    $this->info($e);
                }
            }
        }
    }
}
