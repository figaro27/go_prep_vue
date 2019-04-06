<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealPackage extends Model
{
    public $fillable = ['title', 'description', 'store_id', 'price'];

    public function meals()
    {
        return $this->belongsToMany('App\\Meal')->withPivot('quantity');
    }

    public static function store($props)
    {
        $props = collect($props)->only([
            'active',
            'featured_image',
            'title',
            'description',
            'price',
            'meals',
            'store_id'
        ]);

        $package = MealPackage::create(
            $props->except('featured_image')->toArray()
        );

        if ($props->has('featured_image')) {
            $imagePath = Utils\Images::uploadB64(
                $props->get('featured_image'),
                'path',
                'meals/'
            );
            $fullImagePath = \Storage::disk('public')->path($imagePath);
            $package->clearMediaCollection('featured_image');
            $package
                ->addMedia($fullImagePath)
                ->toMediaCollection('featured_image');
        }

        // Associate meals
        foreach ($props->get('meals') as $meal) {
            MealPackageMeal::create([
                'meal_id' => $meal['id'],
                'meal_package_id' => $package->id,
                'quantity' => $meal['quantity']
            ]);
        }

        return $package;
    }

    public function _update($props)
    {
        $props = collect($props)->only([
            'active',
            'featured_image',
            'title',
            'description',
            'price',
            'meals'
        ]);

        if ($props->has('featured_image')) {
            $imagePath = Utils\Images::uploadB64(
                $props->get('featured_image'),
                'path',
                'meals/'
            );
            $fullImagePath = \Storage::disk('public')->path($imagePath);
            $this->clearMediaCollection('featured_image');
            $this->addMedia($fullImagePath)->toMediaCollection(
                'featured_image'
            );
        }

        $this->update($props->except('featured_image')->toArray());

        return $this;
    }

    public static function updateActive($id, $active)
    {
        $meal = Meal::where('id', $id)->first();

        $meal->update([
            'active' => $active
        ]);
    }

    public static function _delete($id, $subId = null)
    {
        $meal = Meal::find($id);
        $sub = Meal::find($subId);
        $store = $meal->store;

        if ($sub) {
            $subscriptionMeals = MealSubscription::where([
                ['meal_id', $meal->id]
            ])->get();

            $subscriptionMeals->each(function ($subscriptionMeal) use (
                $meal,
                $sub,
                $subId
            ) {
                if (!$subscriptionMeal->subscription) {
                    return;
                }
                $subscriptionMeal->meal_id = $subId;
                try {
                    $subscriptionMeal->save();
                } catch (\Illuminate\Database\QueryException $e) {
                    $errorCode = $e->errorInfo[1];

                    // If this subscription already has the same meal
                    // add to the quantity
                    if ($errorCode == 1062) {
                        $qty = $subscriptionMeal->quantity;
                        $subscriptionMeal->delete();

                        $subscriptionMeal = MealSubscription::where([
                            ['meal_id', $subId],
                            [
                                'subscription_id',
                                $subscriptionMeal->subscription_id
                            ]
                        ])->first();

                        $subscriptionMeal->quantity += $qty;
                        $subscriptionMeal->save();
                    }
                }
                $subscriptionMeal->subscription->syncPrices();

                $user = $subscriptionMeal->subscription->user;

                if ($user) {
                    $user->sendNotification('subscription_meal_substituted', [
                        'user' => $user,
                        'customer' => $subscriptionMeal->subscription->customer,
                        'subscription' => $subscriptionMeal->subscription,
                        'old_meal' => $meal,
                        'sub_meal' => $sub,
                        'store' => $subscriptionMeal->subscription->store
                    ]);
                }
            });
        }

        $meal->delete();
    }

    public static function generateImageFilename($image, $ext)
    {
        return sha1($image) . '.' . $ext;
    }
}
