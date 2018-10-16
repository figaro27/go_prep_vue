<?php

use Illuminate\Database\Seeder;

class UserRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_roles')->insert([
        	'id' => 1,
            'type' => 'customer'
        ]);

        DB::table('user_roles')->insert([
        	'id' => 2,
            'type' => 'store'
        ]);

        DB::table('user_roles')->insert([
        	'id' => 3,
            'type' => 'admin'
        ]);
    }
}
