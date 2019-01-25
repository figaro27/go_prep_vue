<?php

use Illuminate\Database\Seeder;
use App\Allergy;

class AllergiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      Allergy::insert([
        ['title' => 'Shellfish'],
        ['title' => 'Soy'],
        ['title' => 'Milk'],
        ['title' => 'Gluten'],
        ['title' => 'Eggs'],
        ['title' => 'Fish'],
        ['title' => 'Nuts'],
        ['title' => 'Wheat'],
      ]);
    }
}
