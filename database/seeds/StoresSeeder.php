<?php

use Faker\Factory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for ($i = 1; $i <= 10; $i++) {
            DB::table('stores')->insert([
                'user_id' => $i,
                'accepted_toa' => 1
            ]);

            DB::table('store_details')->insert([
                'store_id' => $i,
                'name' => $faker->company,
                'phone' => $faker->phoneNumber,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'zip' => 11204,
                'logo' => '/images/store/store-logo.jpg',
                'domain' => 'store' . $i
            ]);

            DB::table('categories')->insert([
                'store_id' => $i,
                'category' => 'Entrees',
                'order' => 0
            ]);
            DB::table('categories')->insert([
                'store_id' => $i,
                'category' => 'Breakfast',
                'order' => 1
            ]);
            DB::table('categories')->insert([
                'store_id' => $i,
                'category' => 'Snacks',
                'order' => 2
            ]);

            DB::table('store_settings')->insert([
                'store_id' => $i,
                'open' => true,
                'minimumOption' => 'price',
                'minimumPrice' => 50,
                'delivery_days' => '["sun"]',
                'cutoff_days' => 1,
                'cutoff_hours' => 0,
                'transferType' => 'delivery,pickup',
                'delivery_distance_type' => 'zipcodes',
                'delivery_distance_radius' => 5,
                'delivery_distance_zipcodes' =>
                    '[11209, 11228, 11214, 11219, 11230, 11218, 11220, 11204]',
                'applyDeliveryFee' => true,
                'showNutrition' => true,
                'showIngredients' => true,
                'deliveryFeeType' => 'flat',
                'deliveryFee' => 1.5,
                'mileageBase' => 3,
                'mileagePerMile' => 1,
                'applyProcessingFee' => true,
                'processingFee' => 1.0,
                'applyMealPlanDiscount' => true,
                'mealPlanDiscount' => 10,
                'deliveryInstructions' =>
                    'Our driver will drop off the food between 4 PM and 6 PM. He will wait 10 minutes after ringing your bell and calling your phone. If you missed your delivery please call us at (555) 123-4567.',
                'pickupInstructions' =>
                    'Please pick up your food at 555 Bay Ridge Ave between 4 PM and 6 PM.',
                'notesForCustomer' =>
                    'Please heat up each hot meal in the microwave for 2 minutes. Do not eat any salads or meals containing dairy after 4 days. Thank you for your order!',
                'stripe_id' => 'acct_1DytLMHoLjZBBJiv',
                'stripe_account' =>
                    '{"access_token":"sk_test_keZlgc38s70oGly6mjmYufLg","livemode":false,"refresh_token":"rt_ERmvvKxytr1o6Abs7dAAAgITMDlccJAX4qcOlPuadA8epbsX","token_type":"bearer","stripe_publishable_key":"pk_test_4AphKydmoFc290wUOgLPJRnY","stripe_user_id":"acct_1DytLMHoLjZBBJiv","scope":"express"}',
                'notifications' => json_encode([
                    'new_order' => true,
                    'new_subscription' => true,
                    'cancelled_subscription' => true,
                    'ready_to_print' => true
                ]),
                'view_delivery_days' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
