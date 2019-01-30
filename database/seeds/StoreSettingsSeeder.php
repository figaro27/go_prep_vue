<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StoreSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('store_settings')->insert([
            'store_id' => 1,
            'minimum' => 5,
            'delivery_days' => '["mon","wed","fri","tue","sun","thu","sat"]',
            'cutoff_days' => 1,
            'cutoff_hours' => 0,
            'delivery_distance_type' => 'zipcodes',
            'delivery_distance_radius' => 500,
            'delivery_distance_zipcodes' => '[11209]',
            // 'stripe_id' => 'acct_1DsNh6GjtVvEbWjh',
            // 'stripe_account' => '{"access_token":"sk_test_hKtcOHTpFM8KdQLjBlvYqTr8","livemode":false,"refresh_token":"rt_EL3ofmpyZNiFniC7w3PUahgbjHQf0sImSTs0mkKMssZr2G2p","token_type":"bearer","stripe_publishable_key":"pk_test_QqLPuymz1GAGAbkLUkEMnTaO","stripe_user_id":"acct_1DsNh6GjtVvEbWjh","scope":"express"}',
            'notifications' => json_encode([
                'new_orders' => true,
                'new_subscriptions' => true,
                'cancelled_subscriptions' => true,
                'ready_to_print' => true,
            ]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
