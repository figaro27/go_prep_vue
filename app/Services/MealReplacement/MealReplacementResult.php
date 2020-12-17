<?php
namespace App\Services\MealReplacement;

use Illuminate\Support\Collection;

class MealReplacementResult
{
    /**
     * MealPackages updated
     *
     * @var Collection
     */
    public $packageMeals;

    /**
     * MealPackageSizes updated
     *
     * @var Collection
     */
    public $packageMealSizes;

    /**
     * MealPackageComonentOptions updated
     *
     * @var Collection
     */
    public $packageMealComponentOptions;

    /**
     * MealPackageAddons updated
     *
     * @var Collection
     */
    public $packageMealAddons;

    /**
     *
     */
    public function __construct()
    {
        $this->packageMeals = new Collection();
        $this->packageMealSizes = new Collection();
        $this->packageMealComponentOptions = new Collection();
        $this->packageMealAddons = new Collection();
    }
}
