<?php

use Illuminate\Database\Seeder;
use Faker\Factory;


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
            'logo' => $faker->word,
            'domain' => 'store',
            'created_at' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
          ]);
          $u->storeDetail()->save($storeDetail);
        });

        factory(App\Store::class, 10)->create()->each(function($u) {
            $u->storeDetail()->save(factory(App\StoreDetail::class)->make());
          });
    }
}
