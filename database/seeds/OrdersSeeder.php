<?php

use App\User;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Main test customer
        $user = User::find(3);

        if (!$user->hasStoreCustomer(1)) {
            $user->createStoreCustomer(1);
        }
        $customer = $user->getStoreCustomer(1, false);

        for ($i = 0; $i < 10; $i++) {
            factory(App\Order::class)->create([
                'customer_id' => $customer->id,
                'user_id' => $user->id,
            ]);
        }

        // Others
        $users = User::where([
            ['user_role_id', 1],
            ['id', '<>', 3],
        ])->get();

        foreach ($users as $user) {
            try {
                $user->createStoreCustomer(1);
                $customer = $user->getStoreCustomer(1, false);

                for ($i = 0; $i < 2; $i++) {
                    factory(App\Order::class)->create([
                        'customer_id' => $customer->id,
                        'user_id' => $user->id,
                    ]);
                }
            } catch (\Exception $e) {}
        }
    }

}
