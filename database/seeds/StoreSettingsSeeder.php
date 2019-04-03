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
            'open' => true,
            'minimumOption' => 'price',
            'minimumPrice' => 50,
            'delivery_days' => '["sun"]',
            'cutoff_days' => 1,
            'cutoff_hours' => 0,
            'transferType' => 'delivery,pickup',
            'delivery_distance_type' => 'zipcodes',
            'delivery_distance_radius' => 5,
            'delivery_distance_zipcodes' => '[11209, 11228, 11214, 11219, 11230, 11218, 11220, 11204]',
            'applyDeliveryFee' => true,
            'showNutrition' => true,
            'showIngredients' => true,
            'deliveryFee' => 1.50,
            'applyProcessingFee' => true,
            'processingFee' => 1.00,
            'applyMealPlanDiscount' => true,
            'mealPlanDiscount' => 10,
            'deliveryInstructions' => 'Our driver will drop off the food between 4 PM and 6 PM. He will wait 10 minutes after ringing your bell and calling your phone. If you missed your delivery please call us at (555) 123-4567.',
            'pickupInstructions' => 'Please pick up your food at 555 Bay Ridge Ave between 4 PM and 6 PM.',
            'notesForCustomer' => 'Please heat up each hot meal in the microwave for 2 minutes. Do not eat any salads or meals containing dairy after 4 days. Thank you for your order!',
            'stripe_id' => 'acct_1DytLMHoLjZBBJiv',
            'stripe_account' => '{"access_token":"sk_test_keZlgc38s70oGly6mjmYufLg","livemode":false,"refresh_token":"rt_ERmvvKxytr1o6Abs7dAAAgITMDlccJAX4qcOlPuadA8epbsX","token_type":"bearer","stripe_publishable_key":"pk_test_4AphKydmoFc290wUOgLPJRnY","stripe_user_id":"acct_1DytLMHoLjZBBJiv","scope":"express"}',
            'notifications' => json_encode([
                'new_order' => true,
                'new_subscription' => true,
                'cancelled_subscription' => true,
                'ready_to_print' => true,
            ]),
            'view_delivery_days' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
