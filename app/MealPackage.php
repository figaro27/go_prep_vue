<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class MealPackage extends Model implements HasMedia
{
    use HasMediaTrait;

    public $fillable = ['title', 'description', 'store_id', 'price', 'active'];
    public $appends = ['image'];

    protected $casts = [
        'price' => 'double',
        'active_orders_price' => 'decimal:2',
        'created_at' => 'date:F d, Y',
        'created_at_local' => 'date:F d, Y'
    ];

    public function meals()
    {
        return $this->belongsToMany('App\\Meal')->withPivot('quantity');
    }

    public function store()
    {
        return $this->belongsTo('App\\Store');
    }

    public function sizes()
    {
        return $this->hasMany('App\MealPackageSize', 'id');
    }

    public function components()
    {
        return $this->hasMany('App\MealPackageComponent', 'id');
    }

    public function selections()
    {
        return $this->hasMany('App\MealPackageSelections', 'id');
    }

    public function addons()
    {
        return $this->hasMany('App\MealPackageAddon', 'id');
    }

    public function getImageAttribute()
    {
        $mediaItems = $this->getMedia('featured_image');

        if (!count($mediaItems)) {
            $url = asset('images/defaultMeal.jpg');

            return [
                'url' => $url,
                'url_thumb' => $url,
                'url_medium' => $url
            ];
        }

        return [
            'url' => $this->store->getUrl($mediaItems[0]->getUrl('full')),
            'url_thumb' => $this->store->getUrl(
                $mediaItems[0]->getUrl('thumb')
            ),
            'url_medium' => $this->store->getUrl(
                $mediaItems[0]->getUrl('medium')
            )
        ];
    }

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('full')
            ->width(1024)
            ->height(1024)
            ->performOnCollections('featured_image');

        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 180, 180)
            ->performOnCollections('featured_image');

        $this->addMediaConversion('medium')
            ->fit(Manipulations::FIT_CROP, 360, 360)
            ->performOnCollections('featured_image');
    }

    public static function _store($props)
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
