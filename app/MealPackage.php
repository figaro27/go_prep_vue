<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\DeliveryDayMealPackage;

class MealPackage extends Model implements HasMedia
{
    use HasMediaTrait;
    use SoftDeletes;

    public $fillable = [
        'title',
        'description',
        'store_id',
        'price',
        'active',
        'default_size_title',
        'meal_carousel'
    ];
    public $appends = ['image', 'category_ids', 'delivery_day_ids'];
    public $hidden = ['store', 'categories'];

    protected $casts = [
        'price' => 'double',
        // 'active_orders_price' => 'decimal:2',
        'created_at' => 'date:F d, Y',
        'created_at_local' => 'date:F d, Y',
        'meal_carousel' => 'boolean',
        'dividePriceByComponents' => 'boolean'
    ];

    public function days()
    {
        return $this->hasMany(
            'App\DeliveryDayMealPackage',
            'meal_package_id',
            'id'
        );
    }

    public function meals()
    {
        return $this->belongsToMany('App\\Meal')
            ->withPivot(['meal_size_id', 'quantity', 'delivery_day_id'])
            ->using('App\\MealMealPackage');
    }

    public function store()
    {
        return $this->belongsTo('App\\Store');
    }

    public function sizes()
    {
        return $this->hasMany('App\MealPackageSize', 'meal_package_id', 'id');
    }

    public function meal_package_orders()
    {
        return $this->belongsTo('App\MealPackageOrder');
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categories->pluck('id');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category')->using(
            'App\MealPackageCategory'
        );
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
            if ($this->store->settings->menuStyle === 'text') {
                return null;
            }
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

    public function getDeliveryDayIdsAttribute()
    {
        $ddays = $this->days->pluck('delivery_day_id');
        return $ddays;
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
            'addons',
            'meal_carousel',
            'category_ids',
            'delivery_day_ids'
        ]);

        $package = MealPackage::create(
            $props
                ->except(['featured_image', 'category_ids', 'delivery_day_ids'])
                ->toArray()
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

        $categories = $props->get('category_ids');
        if (is_array($categories)) {
            $package->categories()->sync($categories);
        }

        $days = $props->get('delivery_day_ids');
        if (is_array($days)) {
            $ddays = DeliveryDayMealPackage::where(
                'meal_package_id',
                $package->id
            )->get();
            foreach ($ddays as $dday) {
                $dday->delete();
            }
            foreach ($days as $day) {
                $dday = new DeliveryDayMealPackage();
                $dday->delivery_day_id = $day;
                $dday->meal_package_id = $package->id;
                $dday->store_id = $this->store_id;
                $dday->save();
            }
        }

        // Associate meals
        foreach ($props->get('meals') as $meal) {
            MealPackageMeal::create([
                'meal_id' => $meal['id'],
                'meal_size_id' => $meal['meal_size_id'] ?? null,
                'meal_package_id' => $package->id,
                'quantity' => $meal['quantity'],
                'delivery_day_id' => $meal['delivery_day_id'] ?? null
            ]);
        }

        $sizes = $props->get('sizes');
        $sizeIds = collect();

        if (is_array($sizes)) {
            foreach ($sizes as $size) {
                if (isset($size['id'])) {
                    $mealPackageSize = $package->sizes()->find($size['id']);
                }

                if (!$mealPackageSize) {
                    $mealPackageSize = new MealPackageSize();
                    $mealPackageSize->meal_package_id = $package->id;
                    $mealPackageSize->store_id = $package->store_id;
                }

                $mealPackageSize->title = $size['title'];
                $mealPackageSize->price = $size['price'];
                //$mealPackageSize->multiplier = $size['multiplier'];
                $count = MealPackageSize::where([
                    'store_id' => $props['store_id'],
                    'title' => $size['title'],
                    'price' => $size['price']
                ])->count();
                if ($count === 0) {
                    $mealPackageSize->save();
                }

                $meals = [];
                foreach ($size['meals'] as $meal) {
                    $meals[$meal['id']] = [
                        'quantity' => $meal['quantity'],
                        'meal_size_id' => $meal['meal_size_id'] ?? null,
                        'delivery_day_id' => $meal['delivery_day_id'] ?? null
                    ];
                }
                $mealPackageSize->meals()->sync($meals);

                $sizeIds->put($size['id'], $mealPackageSize->id);
            }

            // Deleted sizes
            $package
                ->sizes()
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
                    $mealPackageComponent = $package
                        ->components()
                        ->find($component['id']);
                }

                if (!$mealPackageComponent) {
                    $mealPackageComponent = new MealPackageComponent();
                    $mealPackageComponent->meal_package_id = $package->id;
                    $mealPackageComponent->store_id = $package->store_id;
                }

                $mealPackageComponent->title = $component['title'];
                $mealPackageComponent->minimum = $component['minimum'];
                $mealPackageComponent->maximum = $component['maximum'];
                $mealPackageComponent->price = $component['price']
                    ? $component['price']
                    : 0;
                $mealPackageComponent->delivery_day_id = isset(
                    $component['delivery_day_id']
                )
                    ? $component['delivery_day_id']
                    : null;
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
                    $option->selectable = $optionArr['selectable'] ?? false;
                    $option->meal_package_size_id = $sizeIds->get(
                        $optionArr['meal_package_size_id'],
                        $optionArr['meal_package_size_id']
                    );
                    $option->save();

                    $meals = [];
                    foreach ($optionArr['meals'] as $meal) {
                        $meals[$meal['id']] = [
                            'quantity' => $meal['quantity'],
                            'meal_size_id' => $meal['meal_size_id'] ?? null,
                            'price' => $meal['price'] ?? 0
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
            $package
                ->components()
                ->whereNotIn('id', $componentIds)
                ->delete();
        }

        // Meal addons
        $addons = $props->get('addons');
        if (is_array($addons)) {
            $addonIds = [];

            foreach ($addons as $addon) {
                if (isset($addon['id'])) {
                    $mealPackageAddon = $package->addons()->find($addon['id']);
                }

                if (!$mealPackageAddon) {
                    $mealPackageAddon = new MealPackageAddon();
                    $mealPackageAddon->meal_package_id = $package->id;
                }

                $mealPackageAddon->title = $addon['title'];
                $mealPackageAddon->price = $addon['price'];
                $mealPackageAddon->meal_package_size_id = $sizeIds->get(
                    $addon['meal_package_size_id'],
                    $addon['meal_package_size_id']
                );
                $mealPackageAddon->selectable = $addon['selectable'] ?? false;
                $mealPackageAddon->delivery_day_id = isset(
                    $addon['delivery_day_id']
                )
                    ? $addon['delivery_day_id']
                    : null;
                $mealPackageAddon->save();

                $meals = [];
                foreach ($addon['meals'] as $meal) {
                    $meals[$meal['id']] = [
                        'quantity' => $meal['quantity'],
                        'meal_size_id' => $meal['meal_size_id'] ?? null,
                        'price' => $meal['price'] ?? 0,
                        'delivery_day_id' => $meal['delivery_day_id'] ?? null
                    ];
                }
                $mealPackageAddon->meals()->sync($meals);

                $addonIds[] = $mealPackageAddon->id;
            }

            // Deleted addons
            $package
                ->addons()
                ->whereNotIn('id', $addonIds)
                ->delete();
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
            'addons',
            'meal_carousel',
            'category_ids',
            'delivery_day_ids'
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

        $categories = $props->get('category_ids');
        if (is_array($categories)) {
            $this->categories()->sync($categories);
        }

        $days = $props->get('delivery_day_ids');
        if (is_array($days)) {
            $ddays = DeliveryDayMealPackage::where(
                'meal_package_id',
                $this->id
            )->get();
            foreach ($ddays as $dday) {
                $dday->delete();
            }
            foreach ($days as $day) {
                $dday = new DeliveryDayMealPackage();
                $dday->delivery_day_id = $day;
                $dday->meal_package_id = $this->id;
                $dday->store_id = $this->store_id;
                $dday->save();
            }
        }

        // Associate meals
        $meals = [];
        $rawMeals = $props->get('meals');

        if (is_array($rawMeals)) {
            foreach ($rawMeals as $rawMeal) {
                $meals[$rawMeal['id']] = [
                    'quantity' => $rawMeal['quantity'],
                    'meal_size_id' => $rawMeal['meal_size_id'] ?? null,
                    'delivery_day_id' => $rawMeal['delivery_day_id'] ?? null
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

                $count = MealPackageSize::where([
                    'store_id' => $this->store_id,
                    'title' => $size['title'],
                    'price' => $size['price']
                ])->count();
                if ($count === 0) {
                    $mealPackageSize->save();
                }

                $meals = [];
                foreach ($size['meals'] as $meal) {
                    $meals[$meal['id']] = [
                        'quantity' => $meal['quantity'],
                        'meal_size_id' => $meal['meal_size_id'] ?? null,
                        'delivery_day_id' => $meal['delivery_day_id'] ?? null
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
                $mealPackageComponent->price = $component['price']
                    ? $component['price']
                    : 0;
                $mealPackageComponent->delivery_day_id = isset(
                    $component['delivery_day_id']
                )
                    ? $component['delivery_day_id']
                    : 69;
                $mealPackageComponent->save();

                $optionIdMap = [];

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
                    $option->selectable = $optionArr['selectable'] ?? false;
                    $option->meal_package_size_id = $sizeIds->get(
                        $optionArr['meal_package_size_id'],
                        $optionArr['meal_package_size_id']
                    );
                    $option->save();

                    // Store ID from creation ID
                    $optionIdMap[$optionArr['id']] = $option->id;

                    $meals = [];
                    foreach ($optionArr['meals'] as $meal) {
                        $meals[$meal['id']] = [
                            'quantity' => $meal['quantity'],
                            'meal_size_id' => $meal['meal_size_id'] ?? null,
                            'price' => $meal['price'] ?? 0
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

                // Get resulting options
                $options = $mealPackageComponent
                    ->options()
                    ->get()
                    ->keyBy('id');

                // Loop through options array again
                foreach ($component['options'] as $optionArr) {
                    // Get real ID and model
                    $optionId = $optionIdMap[$optionArr['id']];
                    $option = $options->get($optionId, null);

                    // Get real restrict option ID
                    $restrictOptionId =
                        $optionArr['restrict_meals_option_id'] ?? null;
                    if (
                        $restrictOptionId &&
                        isset($optionIdMap[$restrictOptionId])
                    ) {
                        $restrictOptionId = $optionIdMap[$restrictOptionId];
                    }

                    if ($option) {
                        // Set the restrict option
                        $option->restrict_meals_option_id =
                            $restrictOptionId ?? null;
                        $option->save();
                    }
                }
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
                $mealPackageAddon->selectable = $addon['selectable'] ?? false;
                $mealPackageAddon->delivery_day_id = isset(
                    $addon['delivery_day_id']
                )
                    ? $addon['delivery_day_id']
                    : null;
                $mealPackageAddon->save();

                $meals = [];
                foreach ($addon['meals'] as $meal) {
                    $meals[$meal['id']] = [
                        'quantity' => $meal['quantity'],
                        'meal_size_id' => $meal['meal_size_id'] ?? null,
                        'price' => $meal['price'] ?? 0
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
