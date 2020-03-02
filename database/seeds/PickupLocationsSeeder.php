<?php

use Illuminate\Database\Seeder;

class PickupLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $name = ['Harbor Fitness', 'NYSC', 'Park Slope Kitchen'];

        $address = ['9312 3rd Ave', '2014 Bay Parkway', '312 Union St'];

        $zip = ['11209', '11204', '11228'];

        for ($u = 1; $u <= 30; $u++) {
            for ($i = 0; $i <= 2; $i++) {
                DB::table('pickup_locations')->insert([
                    'store_id' => $u,
                    'name' => $name[$i],
                    'address' => $address[$i],
                    'city' => 'Brooklyn',
                    'state' => 'NY',
                    'zip' => $zip[$i]
                ]);
            }
        }
    }
}
