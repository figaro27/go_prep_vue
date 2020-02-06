<?php

use Illuminate\Database\Seeder;
use App\User;
use Carbon\Carbon;

class GiftCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $title = ['50 Dollar Gift Card', '100 Dollar Gift card'];

        $price = [50, 100];

        $daysAgo = [
            Carbon::now()->subDays(3),
            Carbon::now()->subDays(5),
            Carbon::now()->subDays(7)
        ];

        for ($u = 1; $u <= 30; $u++) {
            for ($i = 0; $i <= 1; $i++) {
                DB::table('gift_cards')->insert([
                    'store_id' => $u,
                    'active' => 1,
                    'title' => $title[$i],
                    'price' => $price[$i]
                ]);
            }
        }

        for ($u = 1; $u <= 30; $u++) {
            for ($i = 0; $i <= 4; $i++) {
                $purchasedGCPrice = $price[rand(0, 1)];
                DB::table('purchased_gift_cards')->insert([
                    'store_id' => $u,
                    'user_id' => $u + $i + 30,
                    'order_id' => 1,
                    'code' => strtoupper(
                        substr(uniqid(rand(10, 99), false), 0, 6)
                    ),
                    'amount' => $purchasedGCPrice,
                    'balance' => $purchasedGCPrice,
                    'emailRecipient' => User::where('id', $u + $i + 30)
                        ->pluck('email')
                        ->first(),
                    'created_at' => $daysAgo[rand(0, 2)]
                ]);
            }
        }
    }
}
