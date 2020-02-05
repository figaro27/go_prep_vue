<?php

use Illuminate\Database\Seeder;

class ProductionGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $title = [
            'Chicken',
            'Turkey',
            'Beef',
            'Fish',
            'Vegetables',
            'Breakfast',
            'Snacks'
        ];

        for ($u = 1; $u <= 30; $u++) {
            for ($i = 0; $i <= 7; $i++) {
                DB::table('production_groups')->insert([
                    'store_id' => $u,
                    'title' => $title[$i]
                ]);
            }
        }
    }
}
