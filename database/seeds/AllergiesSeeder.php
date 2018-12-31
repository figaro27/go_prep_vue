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
        ['allergy' => 'Nuts'],
        ['allergy' => 'Shellfish'],
        ['allergy' => 'Soy'],
        ['allergy' => 'Lactose'],
      ]);
    }
}
