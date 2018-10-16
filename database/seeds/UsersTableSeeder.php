<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'role' => 1,
            'email' => 'customer@goprep.com',
            'password' => bcrypt('secret'),
        ]);

        DB::table('users')->insert([
            'role' => 2,
            'email' => 'store@goprep.com',
            'password' => bcrypt('secret'),
        ]);

        DB::table('users')->insert([
            'role' => 3,
            'email' => 'admin@goprep.com',
            'password' => bcrypt('secret'),
        ]);
    }
}
