<?php

namespace App;

use App\MealComponent;
use App\MealComponentOption;
use App\MealOrder;
use App\MealSize;
use App\MealMacro;
use App\Store;
use App\Subscription;
use App\Traits\LocalizesDates;
use App\Utils\Data\Format;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Constraint\Exception;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use App\Media\Utils as MediaUtils;
use App\MealMealPackage;
use App\MealMealPackageSize;
use App\MealMealPackageComponentOption;
use App\MealMealPackageAddon;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\DeliveryDayMeal;
use App\ChildMeal;

class Meal extends Model implements HasMedia
{
    use SoftDeletes;
    use LocalizesDates;
    use HasMediaTrait;

    protected $fillable = [
        'active',
        'featured_image',
        'title',
        'description',
        'instructions',
        'price',
        'default_size_title',
        'created_at',
        'production_group_id',
        'salesTax',
        'stock',
        'expirationDays',
        'frequencyType',
        'hideFromMenu'
    ];

    protected $casts = [
        'price' => 'double',
        // 'active_orders_price' => 'decimal:2',
        'created_at' => 'date:F d, Y',
        'created_at_local' => 'date:F d, Y',
        'substitute' => 'boolean',
        'expirationDays' => 'integer',
        'hideFromMenu' => 'boolean'
    ];

    protected $appends = [
        'allergy_titles',
        'tag_titles',
        // 'nutrition',
        // 'active_orders',
        // 'active_orders_price',
        // 'lifetime_orders',
        // 'subscription_count',
        'allergy_ids',
        'category_ids',
        'tag_ids',
        'delivery_day_ids',
        // 'substitute',
        // 'substitute_ids',
        // 'ingredient_ids',
        // 'order_ids',
        'created_at_local',
        'image',
        'gallery',
        'quantity',
        'meal_size',
        'full_title',

        // Relevant only when meal is connected to an order
        'item_title',
        'item_price',
        'item_quantity',
        'meal_size_id',
        'hasVariations',
        'subItem',
        'child_store_ids'
    ];

    protected $hidden = [
        'allergies',
        'categories',
        'orders',
        'subscriptions',
        'store',
        'componentOptions'
        //'ingredients',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'local_created_at'];

    public function days()
    {
        return $this->hasMany('App\DeliveryDayMeal', 'meal_id', 'id');
    }

    public function getQuantityAttribute()
    {
        if ($this->pivot && $this->pivot->quantity) {
            return $this->pivot->quantity;
        } else {
            return null;
        }
    }

    public function getSubItemAttribute()
    {
        if ($this->frequencyType == 'sub') {
            return true;
        } else {
            return false;
        }
    }

    public function getMealSizeIdAttribute()
    {
        if ($this->pivot && $this->pivot->meal_size_id) {
            return $this->pivot->meal_size_id;
        } else {
            return null;
        }
    }

    public function getMealSizeAttribute()
    {
        if (
            $this->pivot &&
            $this->pivot->meal_size_id
            //&& $this->pivot->meal_size
        ) {
            return MealSize::find($this->pivot->meal_size_id);
        } else {
            return null;
        }
    }

    public function getFullTitleAttribute()
    {
        $title = $this->title;
        if ($this->default_size_title) {
            $title .= ' - ' . $this->default_size_title;
        }
        return $title;
    }

    public function getItemTitleAttribute()
    {
        $title = $this->title;

        if ($this->has('sizes')) {
            if (
                $this->pivot &&
                $this->pivot->meal_size_id &&
                $this->pivot->meal_size
            ) {
                return $title . ' - ' . $this->meal_size->title;
            } elseif ($this->default_size_title) {
                return $title . ' - ' . $this->default_size_title;
            }
        }

        return $title;
    }

    public function getItemPriceAttribute()
    {
        $price = $this->price;

        if ($this->has('sizes')) {
            if (
                $this->pivot &&
                $this->pivot->meal_size_id &&
                $this->pivot->meal_size
            ) {
                $price = $this->meal_size->price;
            }
        }

        if ($this->has('components')) {
            if ($this->pivot && $this->pivot->components) {
                $a = $this->pivot->components;
                foreach ($this->pivot->components as $component) {
                    $price += $component->option->price;
                }
            }
        }

        return $price;
    }

    public function getItemQuantityAttribute()
    {
        if ($this->pivot && $this->pivot->quantity) {
            return $this->pivot->quantity;
        }

        return null;
    }

    /*public function getFeaturedImageAttribute() {
    $mediaItems = $this->getMedia('featured_image');
    return count($mediaItems) ? $mediaItems[0]->getUrl('thumb') : null;
    }*/

    public function getImageAttribute()
    {
        $mediaItems = $this->getMedia('featured_image');

        if (!count($mediaItems) && $this->store) {
            if ($this->store->settings->menuStyle === 'text') {
                return null;
            }

            if ($this->store->storeDetail->logo) {
                return [
                    'url' => $this->store->storeDetail->logo['url'],
                    'url_thumb' => $this->store->storeDetail->logo['url_thumb'],
                    'url_medium' =>
                        $this->store->storeDetail->logo['url_medium']
                ];
            } else {
                $url = asset('images/defaultMeal.jpg');

                return [
                    'url' => $url,
                    'url_thumb' => $url,
                    'url_medium' => $url
                ];
            }
        }

        $media = isset($mediaItems[0]) ? $mediaItems[0] : '';

        return [
            'id' => isset($mediaItems[0]) ? $mediaItems[0]->id : null,
            'url' => $this->store
                ? $this->store->getUrl(MediaUtils::getMediaPath($media))
                : '',
            'url_thumb' => $this->store
                ? $this->store->getUrl(
                    MediaUtils::getMediaPath($media, 'thumb')
                )
                : '',
            'url_medium' => $this->store
                ? $this->store->getUrl(
                    MediaUtils::getMediaPath($media, 'medium')
                )
                : ''
        ];
    }

    public function getGalleryAttribute()
    {
        $mediaItems = $this->getMedia('gallery');

        if (!count($mediaItems)) {
            return [];
        }

        return collect($mediaItems)->map(function ($item) {
            return [
                'id' => $item->id,
                'url' => $this->store->getUrl($item->getUrl('full')),
                'url_original' => $this->store->getUrl($item->getUrl()),
                'url_thumb' => $this->store->getUrl($item->getUrl('thumb')),
                'url_medium' => $this->store->getUrl($item->getUrl('medium'))
            ];
        });
    }

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('full')
            ->width(1024)
            ->height(1024)
            ->performOnCollections('featured_image', 'gallery');

        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 180, 180)
            ->performOnCollections('featured_image', 'gallery');

        $this->addMediaConversion('medium')
            ->fit(Manipulations::FIT_CROP, 360, 360)
            ->performOnCollections('featured_image', 'gallery');
    }

    // public function getLifetimeOrdersAttribute()
    // {
    //     $id = $this->id;
    //     return MealOrder::where('meal_id', $id)
    //         ->whereHas('order', function ($order) {
    //             $order->where('paid', 1);
    //         })
    //         ->get()
    //         ->sum('quantity');
    // }

    // public function getActiveOrdersAttribute()
    // {
    //     return $this->orders->where('fulfilled', 0)->count();
    // }

    // public function getActiveOrdersPriceAttribute()
    // {
    //     return $this->orders->where('fulfilled', 0)->count() * $this->price;
    // }

    // public function getNutritionAttribute()
    // {
    //     $nutrition = [];

    //     foreach ($this->ingredients as $ingredient) {
    //         foreach (Ingredient::NUTRITION_FIELDS as $field) {
    //             if (!array_key_exists($field, $nutrition)) {
    //                 $nutrition[$field] = 0;
    //             }

    //             $nutrition[$field] +=
    //                 $ingredient[$field] * $ingredient->pivot->quantity_grams;
    //         }
    //     }

    //     return $nutrition;
    // }

    public static function getNutrition($id)
    {
        $nutrition = [];

        $meal = Meal::where('id', $id)->first();

        if ($meal) {
            foreach ($meal->ingredients as $ingredient) {
                foreach (Ingredient::NUTRITION_FIELDS as $field) {
                    if (!array_key_exists($field, $nutrition)) {
                        $nutrition[$field] = 0;
                    }

                    $nutrition[$field] +=
                        $ingredient[$field] *
                        $ingredient->pivot->quantity_grams;
                }
            }
        }

        return $nutrition;
    }

    public static function getIngredients($id)
    {
        $meal = Meal::where('id', $id)->first();
        return $meal ? $meal->ingredients : [];
    }

    // public function getOrderIdsAttribute()
    // {
    //     return $this->orders->pluck('id');
    // }

    // public function getIngredientIdsAttribute()
    // {
    //     return $this->ingredients->pluck('id');
    // }

    public function getAllergyIdsAttribute()
    {
        return $this->allergies->pluck('id');
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categories->pluck('id');
    }

    public function getTagIdsAttribute()
    {
        return $this->tags->pluck('id');
    }

    public function getDeliveryDayIdsAttribute()
    {
        $ddays = $this->days->pluck('delivery_day_id');
        return $ddays;
    }

    // public function getSubscriptionCountAttribute()
    // {
    //     return $this->subscriptions
    //         ->where('status', '!=', 'cancelled')
    //         ->count();
    // }

    /**
     * Whether meal must be substituted before deleting
     *
     * @return bool
     */
    // public function getSubstituteAttribute()
    // {
    //     // return $this->orders()->where([
    //     //     ['delivery_date', '>', Carbon::now('utc')],
    //     // ])->count() > 0;
    //     return $this->subscription_count > 0;
    // }

    // public function getSubstituteIdsAttribute()
    // {
    //     if ($this->store) {
    //         $ids = Cache::rememberForever(
    //             'meal_substitutes_' . $this->id,
    //             function () {
    //                 $mealsQuery = $this->store
    //                     ->meals()
    //                     ->where([
    //                         ['id', '<>', $this->id],
    //                         ['price', '<=', $this->price * 1.2],
    //                         ['price', '>=', $this->price * 0.8]
    //                     ])
    //                     ->whereDoesntHave('allergies', function ($query) {
    //                         $allergyIds = $this->allergies->pluck('id');
    //                         $query->whereIn('allergies.id', $allergyIds);
    //                     })
    //                     ->whereHas(
    //                         'categories',
    //                         function ($query) {
    //                             $catIds = $this->categories->pluck('id');
    //                             return $query->whereIn(
    //                                 'categories.id',
    //                                 $catIds
    //                             );
    //                         },
    //                         '>=',
    //                         1
    //                     );

    //                 $meals = $mealsQuery->get();
    //                 // if ($meals->count() <= 5) {
    //                 //     return $meals->pluck('id');
    //                 // }

    //                 // $mealsQuery = $mealsQuery->whereNotIn(
    //                 //     'id',
    //                 //     $meals->pluck('id')
    //                 // );

    //                 // $mealsQuery = $mealsQuery
    //                 //     ->whereHas(
    //                 //         'categories',
    //                 //         function ($query) {
    //                 //             $catIds = $this->categories->pluck('id');
    //                 //             return $query->whereIn('categories.id', $catIds);
    //                 //         },
    //                 //         '>=',
    //                 //         1
    //                 //     );
    //                 // ->orWhereHas(
    //                 //     'tags',
    //                 //     function ($query) {
    //                 //         $tagIds = $this->tags->pluck('id');
    //                 //         return $query->whereIn('meal_tags.id', $tagIds);
    //                 //     },
    //                 //     '>=',
    //                 //     1
    //                 // );

    //                 // $extraMeals = $mealsQuery->get();

    //                 return $meals
    //                     // ->concat($extraMeals)
    //                     ->slice(0, 5)
    //                     ->pluck('id');
    //             }
    //         );

    //         return $ids;
    //     } else {
    //         return null;
    //     }
    // }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function ingredients()
    {
        return $this->belongsToMany('App\Ingredient')
            ->withPivot('quantity', 'quantity_unit', 'quantity_unit_display')
            ->using('App\IngredientMeal');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category')->using('App\MealCategory');
    }

    public function meal_orders()
    {
        return $this->belongsTo('App\MealOrder');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Order', 'meal_orders');
    }

    public function meal_subscriptions()
    {
        return $this->belongsTo('App\MealSubscription');
    }

    public function subscriptions()
    {
        return $this->belongsToMany('App\Subscription', 'meal_subscriptions');
    }

    public function packages()
    {
        return $this->belongsToMany('App\MealPackage', 'meal_meal_package');
    }

    public function sizes()
    {
        return $this->hasMany('App\MealSize', 'meal_id', 'id');
    }

    public function components()
    {
        return $this->hasMany('App\MealComponent', 'meal_id', 'id');
    }

    public function componentOptions()
    {
        return $this->hasManyThrough(
            'App\MealComponentOption',
            'App\MealComponent'
        );
    }

    public function addons()
    {
        return $this->hasMany('App\MealAddon', 'meal_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany('App\MealAttachment');
    }

    public function tags()
    {
        return $this->belongsToMany('App\MealTag', 'meal_meal_tag');
    }

    public function macros()
    {
        return $this->hasOne('App\MealMacro', 'meal_id', 'id');
    }

    public function getTagTitlesAttribute()
    {
        return collect($this->tags)->map(function ($meal) {
            return $meal->tag;
        });
    }

    public function getAllergyTitlesAttribute()
    {
        return collect($this->allergies)->map(function ($meal) {
            return $meal->title;
        });
    }

    public function allergies()
    {
        return $this->belongsToMany(
            'App\Allergy',
            'allergy_meal',
            'meal_id',
            'allergy_id'
        )->using('App\MealAllergy');
    }

    //Admin View
    public static function getMeals()
    {
        return Meal::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($meal) {
                return [
                    "id" => $meal->id,
                    "active" => $meal->active,
                    "featured_image" => $meal->featured_image,
                    "gallery" => $meal->gallery,
                    "title" => $meal->title,
                    "description" => $meal->description,
                    "instructions" => $meal->instructions,
                    "price" => $meal->price,
                    "created_at" => $meal->created_at
                ];
            });
    }

    //Store View
    public static function getStoreMeals($storeID = null)
    {
        return Meal::with('meal_order', 'ingredients', 'meal_tags')
            ->where('store_id', $storeID)
            ->orderBy('active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($meal) {
                // Fix redundancy of getting the authenticated Store ID twice
                $id = Auth::user()->id;
                $storeID = Store::where('user_id', $id)
                    ->pluck('id')
                    ->first();
                return [
                    "id" => $meal->id,
                    "active" => $meal->active,
                    "featured_image" => $meal->featured_image,
                    "gallery" => $meal->gallery,
                    "title" => $meal->title,
                    "description" => $meal->description,
                    "instructions" => $meal->instructions,
                    "price" => '$' . $meal->price,
                    "current_orders" => $meal->meal_order
                        ->where('store_id', $storeID)
                        ->count(),
                    "created_at" => $meal->created_at,
                    'ingredients' => $meal->ingredients,
                    'meal_tags' => $meal->meal_tags,
                    "salesTax" => $meal->salesTax ? $meal->salesTax : null
                ];
            });
    }

    public static function getMeal($id)
    {
        return Meal::with(
            'ingredients',
            'tags',
            'categories',
            'sizes',
            'components',
            'addons',
            'macros',
            'attachments'
        )
            ->where('id', $id)
            ->first();
    }

    public static function storeMeal($request)
    {
        $request->validate([
            'title' => 'required',
            'price' => 'required|numeric',
            'category_ids' => 'required'
        ]);

        $user = auth('api')->user();
        $store = $user->store;

        $props = collect($request->all());
        $props = $props->only([
            'active',
            'featured_image',
            'gallery',
            'photo',
            'title',
            'description',
            'instructions',
            'price',
            'created_at',
            'tag_ids',
            'category_ids',
            'allergy_ids',
            'delivery_day_ids',
            'ingredients',
            'sizes',
            'default_size_title',
            'components',
            'addons',
            'macros',
            'production_group_id',
            'salesTax',
            'stock',
            'expirationDays',
            'servingsPerMeal',
            'servingUnitQuantity',
            'servingSizeUnit',
            'frequencyType',
            'child_store_ids',
            'hideFromMenu'
        ]);

        $meal = new Meal();
        $meal->store_id = $store->id;
        $meal->active = true;
        $meal->title = $props->get('title', '');
        $meal->description = $props->get('description', '');
        $meal->instructions = $props->get('instructions', '');
        $meal->price = $props->get('price', 0);
        $meal->default_size_title = $props->get('default_size_title', '');
        $meal->salesTax = $props->get('salesTax');
        $meal->save();

        $childStoreIds = isset($props['child_store_ids'])
            ? $props['child_store_ids']
            : [];
        foreach ($childStoreIds as $childStoreId) {
            $newChildMeal = new ChildMeal();
            $newChildMeal->meal_id = $meal->id;
            $newChildMeal->store_id = $childStoreId;
            $newChildMeal->save();
        }

        try {
            if ($props->has('featured_image')) {
                $imagePath = Utils\Images::uploadB64(
                    $request->get('featured_image'),
                    'path',
                    'meals/'
                );
                $fullImagePath = \Storage::disk('public')->path($imagePath);
                $meal->clearMediaCollection('featured_image');
                $meal
                    ->addMedia($fullImagePath)
                    ->toMediaCollection('featured_image');

                //$props->put('featured_image', $imageUrl);
            } else {
                $defaultImageUrl = '/images/defaultMeal.jpg';
                $props->put('featured_image', $defaultImageUrl);
            }

            if ($props->has('gallery')) {
                foreach ($props->get('gallery') as $image) {
                    $imagePath = Utils\Images::uploadB64(
                        $image['url'],
                        'path',
                        'meals/'
                    );
                    $fullImagePath = \Storage::disk('public')->path($imagePath);
                    $meal
                        ->addMedia($fullImagePath)
                        ->toMediaCollection('gallery');
                }
            }

            $newIngredients = $props->get('ingredients');
            if (is_array($newIngredients)) {
                $ingredients = collect();

                foreach ($newIngredients as $newIngredient) {
                    try {
                        // Check if ingredient with same name and unit type already exists
                        $ingredient = Ingredient::where([
                            'store_id' => $store->id,
                            'food_name' => $newIngredient['food_name'],
                            'unit_type' => Unit::getType(
                                $newIngredient['serving_unit']
                            )
                        ])->first();

                        if ($ingredient) {
                            $ingredients->push($ingredient);
                        }
                        // Nope. Create a new one
                        else {
                            $newIngredient = collect($newIngredient)
                                ->only([
                                    'food_name',
                                    'photo',
                                    'serving_qty',
                                    'serving_unit',
                                    'serving_weight_grams',
                                    'calories',
                                    'totalfat',
                                    'satfat',
                                    'transfat',
                                    'cholesterol',
                                    'sodium',
                                    'totalcarb',
                                    'fibers',
                                    'sugars',
                                    'proteins',
                                    'vitamind',
                                    'potassium',
                                    'calcium',
                                    'iron',
                                    'sugars',
                                    'image',
                                    'image_thumb',
                                    'hidden'
                                ])
                                ->map(function ($val) {
                                    return is_null($val) ? 0 : $val;
                                });

                            $ingredientArr = Ingredient::normalize(
                                $newIngredient->toArray()
                            );
                            $ingredient = new Ingredient($ingredientArr);
                            $ingredient->store_id = $store->id;
                            if ($ingredient->save()) {
                                $ingredients->push($ingredient);

                                $meal->store->units()->create([
                                    'store_id' => $store->id,
                                    'ingredient_id' => $ingredient->id,
                                    'unit' => $newIngredient->get(
                                        'serving_unit',
                                        Format::baseUnit($ingredient->unit_type)
                                    )
                                ]);
                            } else {
                                throw new \Exception(
                                    'Failed to create ingredient'
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        die($e);
                    }
                }

                $syncIngredients = $ingredients->mapWithKeys(function (
                    $val,
                    $key
                ) use ($newIngredients) {
                    return [
                        $val->id => [
                            'quantity' =>
                                $newIngredients[$key]['quantity'] ?? 1,
                            'quantity_unit' =>
                                $newIngredients[$key]['quantity_unit'] ??
                                Format::baseUnit($val->unit_type),
                            'quantity_unit_display' =>
                                $newIngredients[$key][
                                    'quantity_unit_display'
                                ] ?? Format::baseUnit($val->unit_type)
                        ]
                    ];
                });

                $meal->ingredients()->sync($syncIngredients);
            }

            $allergies = $props->get('allergy_ids');
            if (is_array($allergies)) {
                $meal->allergies()->sync($allergies);
            }

            $categories = $props->get('category_ids');
            if (is_array($categories)) {
                $meal->categories()->sync($categories);
            }

            $tags = $props->get('tag_ids');
            if (is_array($tags)) {
                $meal->tags()->sync($tags);
            }

            $days = $props->get('delivery_day_ids');
            if (is_array($days)) {
                $ddays = DeliveryDayMeal::where('meal_id', $meal->id)->get();
                foreach ($ddays as $dday) {
                    $dday->delete();
                }
                foreach ($days as $day) {
                    $dday = new DeliveryDayMeal();
                    $dday->store_id = $meal->store_id;
                    $dday->delivery_day_id = $day;
                    $dday->meal_id = $meal->id;
                    $dday->save();
                }
            }

            // Meal sizes
            $sizes = $props->get('sizes');
            $sizeIds = collect();
            if (is_array($sizes)) {
                foreach ($sizes as $size) {
                    $mealSize = new MealSize();
                    $mealSize->store_id = $store->id;
                    $mealSize->meal_id = $meal->id;
                    $mealSize->title = $size['title'];
                    $mealSize->full_title =
                        $meal->title . ' - ' . $size['title'];
                    $mealSize->price = $size['price'];
                    $mealSize->multiplier = $size['multiplier'];
                    $mealSize->save();

                    // Map the fake size ID to the real one for components and addons
                    $mealSize->syncIngredients($size['ingredients']);
                    $sizeIds->put($size['id'], $mealSize->id);
                    if (isset($size['servingsPerMeal'])) {
                        $servingsPerMeal = $size['servingsPerMeal'];
                    }
                    if (isset($size['servingUnitQuantity'])) {
                        $servingUnitQuantity = $size['servingUnitQuantity'];
                    }
                    if (isset($size['servingSizeUnit'])) {
                        $servingSizeUnit = $size['servingSizeUnit'];
                    }

                    if (
                        isset($servingsPerMeal) &&
                        isset($servingSizeUnit) &&
                        isset($servingUnitQuantity)
                    ) {
                        Meal::saveMealServings(
                            null,
                            $meal,
                            $servingsPerMeal,
                            $servingUnitQuantity,
                            $servingSizeUnit,
                            $mealSize
                        );
                    }
                }
            }

            // Meal components
            $components = $props->get('components');
            if (is_array($components)) {
                $componentIds = [];
                $optionIds = [];

                foreach ($components as $component) {
                    if (isset($component['id'])) {
                        $mealComponent = $meal
                            ->components()
                            ->find($component['id']);
                    }

                    if (!$mealComponent) {
                        $mealComponent = new MealComponent();
                        $mealComponent->meal_id = $meal->id;
                        $mealComponent->store_id = $meal->store_id;
                    }

                    $mealComponent->title = $component['title'];
                    $mealComponent->minimum = isset($component['minimum'])
                        ? $component['minimum']
                        : 1;
                    $mealComponent->maximum = isset($component['maximum'])
                        ? $component['maximum']
                        : 1;
                    $mealComponent->save();

                    foreach ($component['options'] as $optionArr) {
                        $option = null;

                        if (isset($optionArr['id'])) {
                            $option = $mealComponent
                                ->options()
                                ->find($optionArr['id']);
                        }

                        if (!$option) {
                            $option = new MealComponentOption();
                            $option->meal_component_id = $mealComponent->id;
                            $option->store_id = $mealComponent->store_id;
                        }

                        $option->title = $optionArr['title'];
                        $option->price = $optionArr['price'];
                        $option->meal_size_id = $sizeIds->get(
                            $optionArr['meal_size_id'],
                            null
                        );

                        $option->save();

                        $option->syncIngredients($optionArr['ingredients']);

                        $optionIds[] = $option->id;
                    }

                    $componentIds[] = $mealComponent->id;
                }
            }

            // Meal addons
            $addons = $props->get('addons');
            if (is_array($addons)) {
                $addonIds = [];

                foreach ($addons as $addon) {
                    if (isset($addon['id'])) {
                        $mealAddon = $meal->addons()->find($addon['id']);
                    }

                    if (!$mealAddon) {
                        $mealAddon = new MealAddon();
                        $mealAddon->meal_id = $meal->id;
                        $mealAddon->store_id = $meal->store_id;
                    }

                    $mealAddon->title = $addon['title'];
                    $mealAddon->price = $addon['price'];
                    $mealAddon->meal_size_id = $sizeIds->get(
                        $addon['meal_size_id'],
                        null
                    );

                    $mealAddon->save();

                    $mealAddon->syncIngredients($addon['ingredients']);

                    $addonIds[] = $mealAddon->id;
                }
            }

            $meal->update(
                $props->except(['featured_image', 'gallery'])->toArray()
            );
        } catch (\Exception $e) {
            $meal->delete();
            throw new \Exception($e);
        }

        if ($meal->store->settings->showMacros) {
            $macros = $props->get('macros');
            $calories = isset($macros['calories']) ? $macros['calories'] : null;
            $carbs = isset($macros['carbs']) ? $macros['carbs'] : null;
            $protein = isset($macros['protein']) ? $macros['protein'] : null;
            $fat = isset($macros['fat']) ? $macros['fat'] : null;

            $macro = new MealMacro();

            $macro->store_id = $meal->store->id;
            $macro->meal_id = $meal->id;
            $macro->calories = $calories;
            $macro->carbs = $carbs;
            $macro->protein = $protein;
            $macro->fat = $fat;

            $macro->save();
        }

        $servingsPerMeal = $request->get('servingsPerMeal');
        $servingUnitQuantity = $request->get('servingUnitQuantity');
        $servingSizeUnit = $request->get('servingSizeUnit');
        if (isset($servingsPerMeal) && isset($servingSizeUnit)) {
            Meal::saveMealServings(
                null,
                $meal,
                $servingsPerMeal,
                $servingUnitQuantity,
                $servingSizeUnit,
                null
            );
        }
    }

    public static function storeMealAdmin($request)
    {
        $meal = new Meal();
        $meal->active = true;
        $meal->store_id = $request->store_id;
        $meal->featured_image = $request->featured_image;
        $meal->title = $request->title;
        $meal->description = $request->description;
        $meal->instructions = $request->instructions;
        $meal->price = $request->price;

        $meal->save();
    }

    public static function updateMeal($id, $props, $source = '')
    {
        $meal = Meal::where('id', $id)->first();

        if (!$meal) {
            return;
        }

        if ($source == 'menu') {
            $store = $meal->store;

            if ($store) {
                $store->setTimezone();
                $store->menu_update_time = date('Y-m-d H:i:s');
                $store->save();
            }
        }

        $props = collect($props)->only([
            'active',
            'featured_image',
            'gallery',
            'photo',
            'title',
            'description',
            'instructions',
            'price',
            'created_at',
            'tag_ids',
            'category_ids',
            'ingredients',
            'allergy_ids',
            'delivery_day_ids',
            'sizes',
            'default_size_title',
            'components',
            'addons',
            'macros',
            'production_group_id',
            'salesTax',
            'stock',
            'expirationDays',
            'frequencyType',
            'child_store_ids',
            'hideFromMenu'
        ]);

        $childStoreIds = isset($props['child_store_ids'])
            ? $props['child_store_ids']
            : [];

        $childMeals = ChildMeal::where('meal_id', $meal->id)->get();
        foreach ($childMeals as $childMeal) {
            if (!in_array($childMeal->store_id, $childStoreIds)) {
                $childMeal->delete();
            }
        }

        foreach ($childStoreIds as $childStoreId) {
            $existingChildMeal = ChildMeal::where([
                'meal_id' => $meal->id,
                'store_id' => $childStoreId
            ])->first();
            if (!$existingChildMeal) {
                $newChildMeal = new ChildMeal();
                $newChildMeal->meal_id = $meal->id;
                $newChildMeal->store_id = $childStoreId;
                $newChildMeal->save();
            }
        }

        if ($props->has('featured_image')) {
            $imagePath = Utils\Images::uploadB64(
                $props->get('featured_image'),
                'path',
                'meals/'
            );
            $fullImagePath = \Storage::disk('public')->path($imagePath);
            $meal->clearMediaCollection('featured_image');
            $meal
                ->addMedia($fullImagePath)
                ->toMediaCollection('featured_image');
            //$props->put('featured_image', $imageUrl);
        }

        if ($props->has('gallery')) {
            $mediaItems = $meal->getMedia('gallery')->keyBy('id');
            $ids = [];

            foreach ($props->get('gallery') as $image) {
                if (isset($image['id']) && $image['id']) {
                    $ids[] = $image['id'];
                    continue;
                }

                $imagePath = Utils\Images::uploadB64(
                    $image['url'],
                    'path',
                    'meals/'
                );
                $fullImagePath = \Storage::disk('public')->path($imagePath);
                $meal->addMedia($fullImagePath)->toMediaCollection('gallery');
            }

            foreach ($mediaItems as $mediaItemId => $mediaItem) {
                if (!in_array($mediaItemId, $ids)) {
                    $mediaItem->delete();
                }
            }
        }

        /*
        $tagTitles = $props->get('tag_titles');
        if (is_array($tagTitles)) {

        $tags = collect();

        foreach ($tagTitles as $tagTitle) {
        try {
        $tag = MealTag::create([
        'tag' => $tagTitle,
        'slug' => str_slug($tagTitle),
        'store_id' => $meal->store_id,
        ]);
        $tags->push($tag->id);
        } catch (\Exception $e) {
        $tags->push(MealTag::where([
        'tag' => $tagTitle,
        'store_id' => $meal->store_id,
        ])->first()->id);
        }
        }

        $meal->tags()->sync($tags);
        }*/

        $newIngredients = $props->get('ingredients');
        if (is_array($newIngredients)) {
            $ingredients = collect();

            foreach ($newIngredients as $newIngredient) {
                try {
                    // Existing ingredient
                    if (
                        is_numeric($newIngredient) ||
                        isset($newIngredient['id'])
                    ) {
                        $ingredientId = is_numeric($newIngredient)
                            ? $newIngredient
                            : $newIngredient['id'];
                        $ingredient = Ingredient::where(
                            'store_id',
                            $meal->store_id
                        )->findOrFail($ingredientId);
                        $ingredients->push($ingredient);
                    } else {
                        $ingredient = Ingredient::fromNutritionix(
                            $meal->id,
                            $newIngredient
                        );

                        if ($ingredient) {
                            $ingredients->push($ingredient);
                        }
                    }
                } catch (\Exception $e) {
                    die($e);
                }
            }

            $syncIngredients = $ingredients->mapWithKeys(function (
                $val,
                $key
            ) use ($newIngredients) {
                return [
                    $val->id => [
                        'quantity' => $newIngredients[$key]['quantity'] ?? 1,
                        'quantity_unit' =>
                            $newIngredients[$key]['quantity_unit'] ??
                            Format::baseUnit($val->unit_type),
                        'quantity_unit_display' =>
                            $newIngredients[$key]['quantity_unit_display'] ??
                            Format::baseUnit($val->unit_type)
                    ]
                ];
            });

            $meal->ingredients()->sync($syncIngredients);
        }

        $allergies = $props->get('allergy_ids');
        if (is_array($allergies)) {
            $meal->allergies()->sync($allergies);
        }

        $categories = $props->get('category_ids');
        if (is_array($categories)) {
            $meal->categories()->sync($categories);
        }

        $tags = $props->get('tag_ids');
        if (is_array($tags)) {
            $meal->tags()->sync($tags);
        }

        $days = $props->get('delivery_day_ids');
        if (is_array($days)) {
            $ddays = DeliveryDayMeal::where('meal_id', $meal->id)->get();
            foreach ($ddays as $dday) {
                $dday->delete();
            }
            foreach ($days as $day) {
                $dday = new DeliveryDayMeal();
                $dday->store_id = $meal->store_id;
                $dday->delivery_day_id = $day;
                $dday->meal_id = $meal->id;
                $dday->save();
            }
        }

        // Meal sizes
        $sizes = $props->get('sizes');
        $sizeIds = collect();

        if (is_array($sizes)) {
            foreach ($sizes as $size) {
                if (isset($size['id'])) {
                    $mealSize = $meal->sizes()->find($size['id']);
                }

                if (!$mealSize) {
                    $mealSize = new MealSize();
                    $mealSize->meal_id = $meal->id;
                }

                $mealSize->store_id = $store->id;
                $mealSize->title = $size['title'];
                $mealSize->full_title = $meal->title . ' - ' . $size['title'];
                $mealSize->price = $size['price'];
                $mealSize->multiplier = $size['multiplier'];
                $mealSize->save();

                $mealSize->syncIngredients($size['ingredients']);

                $sizeIds->put($size['id'], $mealSize->id);
            }

            // Remove the variation from all existing subscriptions before deleting
            // $mealSizes = $meal->sizes()->whereNotIn('id', $sizeIds)->get();
            // Subscription::removeVariations('sizes', $mealSizes);

            // Deleted sizes
            $meal
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
                    $mealComponent = $meal
                        ->components()
                        ->find($component['id']);
                }

                if (!$mealComponent) {
                    $mealComponent = new MealComponent();
                    $mealComponent->meal_id = $meal->id;
                    $mealComponent->store_id = $meal->store_id;
                }

                $mealComponent->title = $component['title'];
                $mealComponent->minimum = isset($component['minimum'])
                    ? $component['minimum']
                    : 1;
                $mealComponent->maximum = isset($component['maximum'])
                    ? $component['maximum']
                    : 1;
                $mealComponent->save();

                foreach ($component['options'] as $optionArr) {
                    $option = null;

                    if (isset($optionArr['id'])) {
                        $option = $mealComponent
                            ->options()
                            ->find($optionArr['id']);
                    }

                    if (!$option) {
                        $option = new MealComponentOption();
                        $option->meal_component_id = $mealComponent->id;
                        $option->store_id = $mealComponent->store_id;
                    }

                    $option->title = $optionArr['title'];
                    $option->price = $optionArr['price'];
                    $option->meal_size_id = $sizeIds->get(
                        $optionArr['meal_size_id'],
                        $optionArr['meal_size_id']
                    );

                    $option->save();

                    $option->syncIngredients($optionArr['ingredients']);

                    $optionIds[] = $option->id;
                }

                $componentIds[] = $mealComponent->id;

                // Remove the variation from all existing subscriptions before deleting
                $mealComponentOptions = $mealComponent
                    ->options()
                    ->whereNotIn('id', $optionIds)
                    ->get();
                Subscription::removeVariations(
                    'componentOptions',
                    $mealComponentOptions
                );

                // Deleted component options
                $mealComponent
                    ->options()
                    ->whereNotIn('id', $optionIds)
                    ->delete();
            }

            // Deleted components

            $mealComponents = $meal
                ->components()
                ->whereNotIn('id', $componentIds)
                ->get();

            foreach ($mealComponents as $mealComponent) {
                Subscription::removeVariations(
                    'componentOptions',
                    $mealComponent->options
                );
                foreach ($mealComponent->options as $option) {
                    $option->delete();
                }
            }

            $meal
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
                    $mealAddon = $meal->addons()->find($addon['id']);
                }

                if (!$mealAddon) {
                    $mealAddon = new MealAddon();
                    $mealAddon->meal_id = $meal->id;
                    $mealAddon->store_id = $meal->store_id;
                }

                $mealAddon->title = $addon['title'];
                $mealAddon->price = $addon['price'];
                $mealAddon->meal_size_id = $sizeIds->get(
                    $addon['meal_size_id'],
                    $addon['meal_size_id']
                );

                $mealAddon->save();

                $mealAddon->syncIngredients($addon['ingredients']);

                $addonIds[] = $mealAddon->id;
            }

            // Deleted addons

            // Remove the variation from all existing subscriptions before deleting
            $mealAddons = $meal
                ->addons()
                ->whereNotIn('id', $addonIds)
                ->get();
            Subscription::removeVariations('addons', $mealAddons);

            $meal
                ->addons()
                ->whereNotIn('id', $addonIds)
                ->delete();
        }

        $meal->update($props->except(['featured_image', 'gallery'])->toArray());

        $macros = $props->get('macros');

        if ($macros && $meal->store->settings->showMacros) {
            $calories = isset($macros['calories']) ? $macros['calories'] : null;
            $carbs = isset($macros['carbs']) ? $macros['carbs'] : null;
            $protein = isset($macros['protein']) ? $macros['protein'] : null;
            $fat = isset($macros['fat']) ? $macros['fat'] : null;

            $macro = MealMacro::where('meal_id', $meal->id)->first();

            if (!$macro) {
                $macro = new MealMacro();
            }

            $macro->store_id = $meal->store->id;
            $macro->meal_id = $meal->id;
            $macro->calories = $calories;
            $macro->carbs = $carbs;
            $macro->protein = $protein;
            $macro->fat = $fat;

            $macro->save();
        }

        return Meal::getMeal($meal->id);
    }

    public static function updateActive($id, $active)
    {
        $meal = Meal::where('id', $id)->first();

        $meal->update([
            'active' => $active
        ]);
    }

    /**
     * @var Collection $substituteMealSizes
     * @var Collection $substituteMealAddons
     * @var Collection $substituteMealComponentOptions
     */
    public static function deleteMeal(
        $id,
        $subId = null,
        $replaceOnly = false,
        $transferVariations = false
    ) {
        $meal = Meal::find($id);
        $sub = Meal::find($subId);
        $store = $meal->store;

        /*
        if ($sub) {
            $sub->active = 1;
            $sub->update();

            // if ($transferVariations) {
            //     foreach ($meal->addons as $addon) {
            //         $mealAddon = $addon->replicate();
            //         $mealAddon->meal_id = $subId;

            //         if ($addon->meal_size_id && $substituteMealSizes) {
            //             $mealAddon->meal_size_id = $substituteMealSizes->get(
            //                 $addon->meal_size_id,
            //                 null
            //             );
            //         }

            //         $mealAddon->push();
            //         $mealAddon->delete();

            //         foreach ($addon->ingredients as $ingredient) {
            //             $addonIngredient = $ingredient->pivot->replicate();
            //             $addonIngredient->meal_addon_id = $mealAddon->id;
            //             $addonIngredient->push();
            //         }

            //         $substituteMealAddons->put($addon->id, $mealAddon->id);
            //     }

            //     foreach ($meal->components as $component) {
            //         $mealComponent = $component->replicate();
            //         $mealComponent->meal_id = $subId;
            //         $mealComponent->push();
            //         $mealComponent->delete();

            //         foreach ($component->options as $option) {
            //             $mealComponentOption = $option->replicate();
            //             $mealComponentOption->meal_component_id =
            //                 $mealComponent->id;

            //             if ($option->meal_size_id && $substituteMealSizes) {
            //                 $mealComponentOption->meal_size_id = $substituteMealSizes->get(
            //                     $option->meal_size_id,
            //                     null
            //                 );
            //             }

            //             $mealComponentOption->push();
            //             $substituteMealComponentOptions->put(
            //                 $option->id,
            //                 $mealComponentOption->id
            //             );

            //             foreach ($option->ingredients as $ingredient) {
            //                 $optionIngredient = $ingredient->pivot->replicate();
            //                 $optionIngredient->meal_component_option_id =
            //                     $mealComponentOption->id;
            //                 $optionIngredient->push();
            //             }
            //         }
            //     }

            //     foreach ($meal->sizes as $size) {
            //         $mealSize = $size->replicate();
            //         $mealSize->meal_id = $subId;
            //         $mealSize->push();
            //         $mealSize->delete();

            //         $ing = IngredientMealSize::where(
            //             'meal_size_id',
            //             $size->id
            //         )->get();

            //         $substituteMealSizes->put($size->id, $mealSize->id);

            //         foreach ($size->ingredients as $ingredient) {
            //             $sizeIngredient = $ingredient->pivot->replicate();
            //             $sizeIngredient->meal_size_id = $mealSize->id;
            //             $sizeIngredient->push();
            //         }
            //     }
            // }

            $subscriptionMeals = MealSubscription::where([
                ['meal_id', $meal->id]
            ])->get();

            $subscriptionMeals->each(function ($subscriptionMeal) use (
                $meal,
                $sub,
                $subId,
                $store,
                $substituteMealSizes,
                $substituteMealAddons,
                $substituteMealComponentOptions
            ) {
                if (!$subscriptionMeal->subscription) {
                    return;
                }

                $price = $sub->price;

                // Substitute size
                if ($subscriptionMeal->meal_size_id) {
                    $subSizeId = $substituteMealSizes->get(
                        $subscriptionMeal->meal_size_id
                    );
                    $subSize = MealSize::find($subSizeId);
                    if ($subSize && $subSize->meal_id !== $subId) {
                        throw new BadRequestHttpException(
                            'Size doesn\'t belong to substitute meal'
                        );
                    }
                    $subscriptionMeal->last_meal_size_id =
                        $subscriptionMeal->meal_size_id;
                    $subscriptionMeal->meal_size_id = $subSizeId;
                    $price = $subSize->price;
                }

                // Substitute components
                foreach ($subscriptionMeal->components as $component) {
                    $option = $component->option;
                    $originalOptionId = $option->id;
                    $subComponentOptionId = $substituteMealComponentOptions->get(
                        $originalOptionId
                    );

                    if (
                        $substituteMealComponentOptions->has($originalOptionId)
                    ) {
                        $subComponentOption = MealComponentOption::find(
                            $subComponentOptionId
                        );

                        if ($subComponentOption) {
                            $component->last_meal_component_id =
                                $component->meal_component_id;
                            $component->last_meal_component_option_id =
                                $component->meal_component_option_id;
                            $component->meal_component_id =
                                $subComponentOption->meal_component_id;
                            $component->meal_component_option_id = $subComponentOptionId;

                            $price += $subComponentOption->price;

                            try {
                                $component->save();
                            } catch (\Exception $e) {
                                if ($e->getCode() === '23000') {
                                    // already has this component. Skip
                                    Log::debug(
                                        'Meal subscription already has component option',
                                        [
                                            'subscription_id' =>
                                                $subscriptionMeal->subscription_id,
                                            'meal_id' =>
                                                $subscriptionMeal->meal_id,
                                            'substitute_meal_id' => $subId,
                                            'meal_component_option_id' => $originalOptionId,
                                            'sub_meal_addon_id' => $subComponentOptionId,
                                            'meal_subscription_id' =>
                                                $subscriptionMeal->id
                                        ]
                                    );
                                    $component->delete();
                                }
                            }
                        }
                    } else {
                        // We have no replacement for this component.
                        // Was the component or option removed from the meal after the subscription created?
                        // Delete it from the subscription
                        $component->delete();

                        Log::error('No component option substitute provided', [
                            'subscription_id' =>
                                $subscriptionMeal->subscription_id,
                            'meal_id' => $subscriptionMeal->meal_id,
                            'substitute_meal_id' => $subId,
                            'meal_component_id' =>
                                $component->meal_component_id,
                            'meal_component_option_id' =>
                                $component->meal_component_option_id,
                            'meal_subscription_id' => $subscriptionMeal->id
                        ]);
                    }
                }

                // Substitute addons
                foreach ($subscriptionMeal->addons as $addon) {
                    if ($substituteMealAddons->has($addon->meal_addon_id)) {
                        $originalMealAddonId = $addon->meal_addon_id;
                        $subAddonId = $substituteMealAddons->get(
                            $originalMealAddonId
                        );
                        $subAddon = MealAddon::find($subAddonId);

                        $addon->last_meal_addon_id = $addon->meal_addon_id;
                        $addon->meal_addon_id = $subAddonId;

                        $price += $subAddon->price;

                        try {
                            $addon->save();
                        } catch (\Exception $e) {
                            if ($e->getCode() === '23000') {
                                // already has this component. Skip
                                Log::debug(
                                    'Meal subscription already has addon',
                                    [
                                        'subscription_id' =>
                                            $subscriptionMeal->subscription_id,
                                        'meal_id' => $subscriptionMeal->meal_id,
                                        'substitute_meal_id' => $subId,
                                        'meal_addon_id' => $originalMealAddonId,
                                        'sub_meal_addon_id' => $subAddonId,
                                        'meal_subscription_id' =>
                                            $subscriptionMeal->id
                                    ]
                                );
                                $addon->delete();
                            }
                        }
                    } else {
                        // We have no replacement for this addon.
                        // Was the addon removed from the meal after the subscription created?
                        // Delete it
                        $addon->delete();

                        Log::error('No addon substitute provided', [
                            'subscription_id' =>
                                $subscriptionMeal->subscription_id,
                            'meal_id' => $subscriptionMeal->meal_id,
                            'substitute_meal_id' => $subId,
                            'meal_addon_id' => $addon->meal_addon_id,
                            'meal_subscription_id' => $subscriptionMeal->id
                        ]);
                    }
                }

                $subscriptionMeal->last_meal_id = $subscriptionMeal->meal_id;
                $subscriptionMeal->meal_id = $subId;

                $subscriptionMeal->price = $price * $subscriptionMeal->quantity;

                $existingSubscriptionMeal = MealSubscription::where([
                    ['meal_id', $subId],
                    ['delivery_date', $subscriptionMeal->delivery_date],
                    ['subscription_id', $subscriptionMeal->subscription_id]
                ])->first();

                if ($existingSubscriptionMeal) {
                    $quantity =
                        $existingSubscriptionMeal->quantity +
                        $subscriptionMeal->quantity;
                    $unitPrice =
                        $existingSubscriptionMeal->price /
                        $existingSubscriptionMeal->quantity;
                    $existingSubscriptionMeal->quantity = $quantity;
                    $existingSubscriptionMeal->price = $unitPrice * $quantity;
                    $existingSubscriptionMeal->delivery_date =
                        $subscriptionMeal->delivery_date;
                    $existingSubscriptionMeal->update();
                    $subscriptionMeal->delete();
                    $existingSubscriptionMeal->subscription->syncPrices(true);
                } else {
                    $subscriptionMeal->save();
                    $subscriptionMeal->fresh()->subscription->syncPrices(true);
                }

                // try {
                //     $subscriptionMeal->save();
                // } catch (\Illuminate\Database\QueryException $e) {
                //     $errorCode = $e->errorInfo[1];

                //     // If this subscription already has the same meal
                //     // add to the quantity
                //     if ($errorCode == 1062) {
                //         $qty = $subscriptionMeal->quantity;
                //         $subscriptionMeal->delete();

                //         $subscriptionMeal = MealSubscription::where([
                //             ['meal_id', $subId],
                //             [
                //                 'subscription_id',
                //                 $subscriptionMeal->subscription_id
                //             ]
                //         ])->first();

                //         $subscriptionMeal->quantity += $qty;
                //         $subscriptionMeal->save();
                //     }
                // }

                // $user = $subscriptionMeal->subscription->user;

                // if ($user) {
                //     $user->sendNotification('subscription_meal_substituted', [
                //         'user' => $user,
                //         'customer' => $subscriptionMeal->subscription->customer,
                //         'subscription' => $subscriptionMeal->subscription,
                //         'old_meal' => $meal,
                //         'sub_meal' => $sub,
                //         'store' => $subscriptionMeal->subscription->store
                //     ]);
                // }
            });
        }*/

        if (!$replaceOnly) {
            $meal->delete();
        } else {
            $meal->active = 0;
            $meal->save();
        }
    }

    public static function generateImageFilename($image, $ext)
    {
        return sha1($image) . '.' . $ext;
    }

    public function getCreatedAtLocalAttribute()
    {
        return $this->localizeDate($this->created_at)->format('F d, Y');
    }

    public function getChildStoreIdsAttribute()
    {
        if ($this->store->childStores) {
            return ChildMeal::where('meal_id', $this->id)
                ->get()
                ->map(function ($childMeal) {
                    return $childMeal->store_id;
                });
        }
    }

    public function getHasVariationsAttribute()
    {
        $hasComponents = $this->components()
            ->whereHas('options')
            ->count();

        if (
            $this->sizes->count() > 0 ||
            $this->addons->count() > 0 ||
            $hasComponents
        ) {
            return true;
        }
    }

    public static function saveMealServings(
        $request,
        $meal,
        $servingsPerMeal,
        $servingUnitQuantity,
        $servingSizeUnit,
        $mealSize
    ) {
        $servingsPerMeal = $request
            ? $request->get('servingsPerMeal')
            : $servingsPerMeal;
        $servingSizeUnit = $request
            ? $request->get('servingSizeUnit')
            : $servingSizeUnit;

        if ($mealSize === null) {
            $meal->servingsPerMeal = $servingsPerMeal;
            if ($servingSizeUnit === null) {
                $meal->servingSizeUnit = '';
            } else {
                $meal->servingSizeUnit = $servingSizeUnit;
            }
            $meal->servingUnitQuantity = $servingUnitQuantity;
            $meal->save();
        } else {
            $mealSize->servingsPerMeal = $servingsPerMeal;

            if ($servingSizeUnit === null) {
                $mealSize->servingSizeUnit = '';
            } else {
                $mealSize->servingSizeUnit = $servingSizeUnit;
            }
            $mealSize->servingUnitQuantity = $servingUnitQuantity;
            $mealSize->update();
        }
    }

    public function activate()
    {
        $this->update(['active' => 1]);
    }
}
