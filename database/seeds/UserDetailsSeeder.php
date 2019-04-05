<?php

use Illuminate\Database\Seeder;

class UserDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for ($i = 1; $i <= 10; $i++) {
            DB::table('user_details')->insert([
                'user_id' => $i,
                'firstname' => 'Mike',
                'lastname' => 'Soldano',
                'phone' => '(917) 334-4487',
                'address' => '244 92nd St',
                'city' => 'Brooklyn',
                'state' => 'NY',
                'zip' => '11209',
                'country' => 'USA',
                'delivery' => 'Ring my doorbell',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        for ($i = 11; $i <= 30; $i++) {
            DB::table('user_details')->insert([
                'user_id' => $i,
                'firstname' => $faker->firstname,
                'lastname' => $faker->lastname,
                'phone' =>
                    '(' .
                    rand(333, 444) .
                    ') ' .
                    rand(123, 999) .
                    '-' .
                    rand(1234, 9999),
                'address' => rand(123, 999) . ' ' . $faker->streetName,
                'city' => $faker->city,
                'state' => 'NY',
                'zip' => '11209',
                'country' => 'USA',
                'delivery' => 'Please ring my doorbell.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
