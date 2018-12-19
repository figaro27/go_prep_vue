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

    	DB::table('user_details')->insert([
            'user_id' => 1,
            'firstname' => 'Herb',
            'lastname' => 'Williams',
            'phone' => '(917) 334-4487',
            'address' => '128 8th Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Ring my doorbell',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('user_details')->insert([
            'user_id' => 2,
            'firstname' => 'Jerry',
            'lastname' => 'McNerry',
            'phone' => '(718) 259-3314',
            'address' => '742 3rd Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Leave on the sidewalk IDC',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('user_details')->insert([
            'user_id' => 3,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 4,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 5,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 6,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 7,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 8,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 9,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 10,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 11,
            'firstname' => 'John',
            'lastname' => 'Buckwith',
            'phone' => '(555) 123-1234',
            'address' => '1874 95th St',
            'city' => 'Manhattan',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('user_details')->insert([
            'user_id' => 12,
            'firstname' => 'Mike',
            'lastname' => 'Soldano',
            'phone' => '(347) 526-9628',
            'address' => '1622 Bay Ridge Ave',
            'city' => 'Brooklyn',
            'state' => 'NY',
            'zip' => '11209',
            'country' => 'USA',
            'delivery' => 'Call Phone',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
