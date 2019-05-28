<?php

use Illuminate\Database\Seeder;
use App\Console\Commands\MigrateImages;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserRolesTableSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(UserDetailsSeeder::class);

        $this->call(StoresSeeder::class);

        $this->call(AllergiesSeeder::class);
        $this->call(IngredientsSeeder::class);
        $this->call(MealsSeeder::class);
        $this->call(MealPackagesSeeder::class);
        //$this->call(MealOrdersSeeder::class);

        //$this->call(OrdersSeeder::class);
        //$this->call(SubscriptionsSeeder::class);
        //$this->call(MealSubscriptionsSeeder::class);
        $this->call(MealTagsSeeder::class);
        $this->call(MealMealTagSeeder::class);
        $this->call(CategoryMealSeeder::class);
        $this->call(AllergyMealSeeder::class);
        $this->call(IngredientMealSeeder::class);

        Artisan::call('migrate:images');
    }
}
