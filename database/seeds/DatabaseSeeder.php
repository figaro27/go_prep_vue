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
        $this->call(UserRolesTableSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(UserDetailsSeeder::class);

        $this->call(StoresSeeder::class);

        $this->call(IngredientsSeeder::class);
        $this->call(MealsSeeder::class);
        $this->call(MealOrdersSeeder::class);

    }
}
