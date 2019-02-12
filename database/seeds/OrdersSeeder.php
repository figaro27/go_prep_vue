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

        $user->orders()->save(
            factory(App\Order::class)->make([
                'customer_id' => $customer->id,
            ])
        );

        // Others
        $users = User::where([
            ['user_role_id', 3],
            ['id', 'NOT', 3],
        ])->get();

        foreach ($users as $user) {
            $user->createStoreCustomer(1);
            $customer = $user->getStoreCustomer(1, false);

            $order = $user->orders()->save(
                factory(App\Order::class)->make([
                    'customer_id' => $customer->id,
                ])
            );
        }
    }

}
