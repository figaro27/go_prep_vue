<?php

use Faker\Factory;
use Illuminate\Database\Seeder;

class StoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Store::class, 1)->create()->each(function ($u) {
            $faker = Faker\Factory::create();
            $storeDetail = new App\StoreDetail([
                'name' => $faker->company,
                'phone' => $faker->phoneNumber,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'zip' => 11204,
                'logo' => $faker->word,
                'domain' => 'store',
                'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now'),
            ]);
            $u->storeDetail()->save($storeDetail);

            $u->categories()->createMany([
                [
                    'store_id' => 1,
                    'category' => 'Breakfast',
                    'order' => 0,
                ],
                [
                    'store_id' => 1,
                    'category' => 'Lunch',
                    'order' => 1,
                ],
                [
                    'store_id' => 1,
                    'category' => 'Dinner',
                    'order' => 2,
                ],
            ]);
        });

        factory(App\Store::class, 10)->create()->each(function ($u) {
            $u->storeDetail()->save(factory(App\StoreDetail::class)->make());
        });
    }
}
