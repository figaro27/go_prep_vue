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
        return $this->hasMany('App\MealPackageSize', 'meal_package_id', 'id');
    }

    public function components()
    {
        return $this->hasMany(
            'App\MealPackageComponent',
            'meal_package_id',
            'id'
        );
    }

    public function selections()
    {
        return $this->hasMany(
            'App\MealPackageSelections',
            'meal_package_id',
            'id'
        );
    }

    public function addons()
    {
        return $this->hasMany('App\MealPackageAddon', 'meal_package_id', 'id');
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
            'store_id',
            'sizes',
            'default_size_title',
            'components',
            'addons'
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
            'meals',
            'sizes',
            'default_size_title',
            'components',
            'addons'
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

        // Associate meals
        $meals = [];
        $rawMeals = $props->get('meals');

        if (is_array($rawMeals)) {
            foreach ($rawMeals as $rawMeal) {
                $meals[$rawMeal['id']] = [
                    'quantity' => $rawMeal['quantity']
                ];
            }

            $this->meals()->sync($meals);
        }

        $sizes = $props->get('sizes');
        $sizeIds = collect();

        if (is_array($sizes)) {
            foreach ($sizes as $size) {
                if (isset($size['id'])) {
                    $mealPackageSize = $this->sizes()->find($size['id']);
                }

                if (!$mealPackageSize) {
                    $mealPackageSize = new MealPackageSize();
                    $mealPackageSize->meal_package_id = $this->id;
                    $mealPackageSize->store_id = $this->store_id;
                }

                $mealPackageSize->title = $size['title'];
                $mealPackageSize->price = $size['price'];
                //$mealPackageSize->multiplier = $size['multiplier'];
                $mealPackageSize->save();

                $meals = [];
                foreach ($size['meals'] as $meal) {
                    $meals[$meal['id']] = [
                        'quantity' => $meal['quantity'],
                        'meal_size_id' => $meal['meal_size_id'] ?? null
                    ];
                }
                $mealPackageSize->meals()->sync($meals);

                $sizeIds->put($size['id'], $mealPackageSize->id);
            }

            // Deleted sizes
            $this->sizes()
                ->whereNotIn('id', $sizeIds)
                ->delete();
        }

        // Meal components
        $components = $props->get('components');
        if (is_array($components)) {
            $componentIds = [];
            $optionIds = [];

            foreach ($components as $component) {
                if (isset($component['id'])) {
                    $mealPackageComponent = $this->components()->find(
                        $component['id']
                    );
                }

                if (!$mealPackageComponent) {
                    $mealPackageComponent = new MealPackageComponent();
                    $mealPackageComponent->meal_package_id = $this->id;
                    $mealPackageComponent->store_id = $this->store_id;
                }

                $mealPackageComponent->title = $component['title'];
                $mealPackageComponent->minimum = $component['minimum'];
                $mealPackageComponent->maximum = $component['maximum'];
                $mealPackageComponent->save();

                foreach ($component['options'] as $optionArr) {
                    $option = null;

                    if (!isset($optionArr['meals'])) {
                        $optionArr['meals'] = [];
                    }

                    if (isset($optionArr['id'])) {
                        $option = $mealPackageComponent
                            ->options()
                            ->find($optionArr['id']);
                    }

                    if (!$option) {
                        $option = new MealPackageComponentOption();
                        $option->meal_package_component_id =
                            $mealPackageComponent->id;
                    }

                    $option->title = $optionArr['title'];
                    $option->price = $optionArr['price'];
                    $option->meal_package_size_id = $sizeIds->get(
                        $optionArr['meal_package_size_id'],
                        $optionArr['meal_package_size_id']
                    );
                    $option->save();

                    $meals = [];
                    foreach ($optionArr['meals'] as $meal) {
                        $meals[$meal['id']] = [
                            'quantity' => $meal['quantity'],
                            'meal_size_id' => $meal['meal_size_id'] ?? null
                        ];
                    }
                    $option->meals()->sync($meals);

                    $optionIds[] = $option->id;
                }

                $componentIds[] = $mealPackageComponent->id;

                // Deleted component options
                $mealPackageComponent
                    ->options()
                    ->whereNotIn('id', $optionIds)
                    ->delete();
            }

            // Deleted components
            $this->components()
                ->whereNotIn('id', $componentIds)
                ->delete();
        }

        // Meal addons
        $addons = $props->get('addons');
        if (is_array($addons)) {
            $addonIds = [];

            foreach ($addons as $addon) {
                if (isset($addon['id'])) {
                    $mealPackageAddon = $this->addons()->find($addon['id']);
                }

                if (!$mealPackageAddon) {
                    $mealPackageAddon = new MealPackageAddon();
                    $mealPackageAddon->meal_package_id = $this->id;
                }

                $mealPackageAddon->title = $addon['title'];
                $mealPackageAddon->price = $addon['price'];
                $mealPackageAddon->meal_package_size_id = $sizeIds->get(
                    $addon['meal_package_size_id'],
                    $addon['meal_package_size_id']
                );
                $mealPackageAddon->save();

                $meals = [];
                foreach ($addon['meals'] as $meal) {
                    $meals[$meal['id']] = [
                        'quantity' => $meal['quantity'],
                        'meal_size_id' => $meal['meal_size_id'] ?? null
                    ];
                }
                $mealPackageAddon->meals()->sync($meals);

                $addonIds[] = $mealPackageAddon->id;
            }

            // Deleted addons
            $this->addons()
                ->whereNotIn('id', $addonIds)
                ->delete();
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
