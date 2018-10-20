<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersSeeder::class);
        $this->call(UserRolesTableSeeder::class);
        $this->call(UserPaymentsSeeder::class);
        $this->call(UserDetailsSeeder::class);

        $this->call(StoresSeeder::class);

        $this->call(OrderSeeder::class);
    }
}
