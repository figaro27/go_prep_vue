<?php

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
        factory(App\Store::class, 10)->create()->each(function($u) {
            $u->storeDetail()->save(factory(App\StoreDetail::class)->make());
          });
    }
}
