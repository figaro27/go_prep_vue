<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'user_role_id' => 1,
            'email' => 'customer@goprep.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'),
            'remember_token' => str_random(10),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'user_role_id' => 2,
            'email' => 'store@goprep.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'),
            'remember_token' => str_random(10),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'user_role_id' => 3,
            'email' => 'admin@goprep.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'),
            'remember_token' => str_random(10),
            'created_at' => now(),
            'updated_at' => now()
        ]);


        factory(App\User::class, 50)->create()->each(function($u) {
            $u->userDetail()->save(factory(App\UserDetail::class)->make());
          });
    }
}
