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
use PHPUnit\Framework\Constraint\Exception;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use App\Media\Utils as MediaUtils;
use App\MealMealPackage;

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
        'production_group_id'
    ];

    protected $casts = [
        'price' => 'double',
        'active_orders_price' => 'decimal:2',
        'created_at' => 'date:F d, Y',
        'created_at_local' => 'date:F d, Y',
        'substitute' => 'boolean'
    ];

    protected $appends = [
        'allergy_titles',
        'tag_titles',
        'nutrition',
        'active_orders',
        'active_orders_price',
        'lifetime_orders',
        'subscription_count',
        'allergy_ids',
        'category_ids',
        'tag_ids',
        'substitute',
        'substitute_ids',
        'ingredient_ids',
        'order_ids',
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
        'in_package',
        'meal_size_id'
    ];

    protected $hidden = [
        'allergies',
        'categories',
        'orders',
        'subscriptions',
        'store'
        //'ingredients',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'local_created_at'];

    public function getQuantityAttribute()
    {
        if ($this->pivot && $this->pivot->quantity) {
            return $this->pivot->quantity;
        } else {
            return null;
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

    public function getInPackageAttribute()
    {
        $mealMealPackages = MealMealPackage::where('meal_id', $this->id)->get();
        if (count($mealMealPackages) > 0) {
            return true;
        } else {
            return false;
        }
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

        if (!count($mediaItems)) {
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

        $media = $mediaItems[0];

        return [
            'id' => $mediaItems[0]->id,
            'url' => $this->store->getUrl(MediaUtils::getMediaPath($media)),
            'url_thumb' => $this->store->getUrl(
                MediaUtils::getMediaPath($media, 'thumb')
            ),
            'url_medium' => $this->store->getUrl(
                MediaUtils::getMediaPath($media, 'medium')
            )
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

    public function getLifetimeOrdersAttribute()
    {
        $id = $this->id;
        return MealOrder::where('meal_id', $id)
            ->whereHas('order', function ($order) {
                $order->where('paid', 1);
            })
            ->get()
            ->sum('quantity');
    }

    public function getActiveOrdersAttribute()
    {
        return $this->orders->where('fulfilled', 0)->count();
    }

    public function getActiveOrdersPriceAttribute()
    {
        return $this->orders->where('fulfilled', 0)->count() * $this->price;
    }

    public function getNutritionAttribute()
    {
        $nutrition = [];

        foreach ($this->ingredients as $ingredient) {
            foreach (Ingredient::NUTRITION_FIELDS as $field) {
                if (!array_key_exists($field, $nutrition)) {
                    $nutrition[$field] = 0;
                }

                $nutrition[$field] +=
                    $ingredient[$field] * $ingredient->pivot->quantity_grams;
            }
        }

        return $nutrition;
    }

    public function getOrderIdsAttribute()
    {
        return $this->orders->pluck('id');
    }

    public function getIngredientIdsAttribute()
    {
        return $this->tags->pluck('id');
    }

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

    public function getSubscriptionCountAttribute()
    {
        return $this->subscriptions->where('status', 'active')->count();
    }

    /**
     * Whether meal must be substituted before deleting
     *
     * @return bool
     */
    public function getSubstituteAttribute()
    {
        // return $this->orders()->where([
        //     ['delivery_date', '>', Carbon::now('utc')],
        // ])->count() > 0;
        return $this->subscription_count > 0;
    }

    public function getSubstituteIdsAttribute()
    {
        $ids = Cache::rememberForever(
            'meal_substitutes_' . $this->id,
            function () {
                $mealsQuery = $this->store
                    ->meals()
                    ->where([
                        ['id', '<>', $this->id],
                        ['price', '<=', $this->price * 1.2],
                        ['price', '>=', $this->price * 0.8]
                    ])
                    ->whereDoesntHave('allergies', function ($query) {
                        $allergyIds = $this->allergies->pluck('id');
                        $query->whereIn('allergies.id', $allergyIds);
                    })
                    ->whereHas(
                        'categories',
                        function ($query) {
                            $catIds = $this->categories->pluck('id');
                            return $query->whereIn('categories.id', $catIds);
                        },
                        '>=',
                        1
                    );

                $meals = $mealsQuery->get();
                // if ($meals->count() <= 5) {
                //     return $meals->pluck('id');
                // }

                // $mealsQuery = $mealsQuery->whereNotIn(
                //     'id',
                //     $meals->pluck('id')
                // );

                // $mealsQuery = $mealsQuery
                //     ->whereHas(
                //         'categories',
                //         function ($query) {
                //             $catIds = $this->categories->pluck('id');
                //             return $query->whereIn('categories.id', $catIds);
                //         },
                //         '>=',
                //         1
                //     );
                // ->orWhereHas(
                //     'tags',
                //     function ($query) {
                //         $tagIds = $this->tags->pluck('id');
                //         return $query->whereIn('meal_tags.id', $tagIds);
                //     },
                //     '>=',
                //     1
                // );

                // $extraMeals = $mealsQuery->get();

                return $meals
                    // ->concat($extraMeals)
                    ->slice(0, 5)
                    ->pluck('id');
            }
        );

        return $ids;
    }

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
                    'meal_tags' => $meal->meal_tags
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
            'price' => 'required|numeric|min:.01',
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
            'ingredients',
            'sizes',
            'default_size_title',
            'components',
            'addons',
            'macros',
            'production_group_id'
        ]);

        $meal = new Meal();
        $meal->store_id = $store->id;
        $meal->active = true;
        $meal->title = $props->get('title', '');
        $meal->description = $props->get('description', '');
        $meal->instructions = $props->get('instructions', '');
        $meal->price = $props->get('price', 0);
        $meal->default_size_title = $props->get('default_size_title', '');
        $meal->save();

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
                                    'totalFat',
                                    'satFat',
                                    'transFat',
                                    'cholesterol',
                                    'sodium',
                                    'totalCarb',
                                    'fibers',
                                    'sugars',
                                    'proteins',
                                    'vitaminD',
                                    'potassium',
                                    'calcium',
                                    'iron',
                                    'sugars',
                                    'image',
                                    'image_thumb'
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

            // Meal sizes
            $sizes = $props->get('sizes');
            $sizeIds = collect();
            if (is_array($sizes)) {
                foreach ($sizes as $size) {
                    $mealSize = new MealSize();
                    $mealSize->meal_id = $meal->id;
                    $mealSize->title = $size['title'];
                    $mealSize->price = $size['price'];
                    $mealSize->multiplier = $size['multiplier'];
                    $mealSize->save();

                    // Map the fake size ID to the real one for components and addons
                    $sizeIds->put($size['id'], $mealSize->id);
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
                    $mealComponent->minimum = $component['minimum'];
                    $mealComponent->maximum = $component['maximum'];
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
            $calories = $macros['calories'];
            $carbs = $macros['carbs'];
            $protein = $macros['protein'];
            $fat = $macros['fat'];

            $macro = new MealMacro();

            $macro->store_id = $meal->store->id;
            $macro->meal_id = $meal->id;
            $macro->calories = $calories;
            $macro->carbs = $carbs;
            $macro->protein = $protein;
            $macro->fat = $fat;

            $macro->save();
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
        $meal = Meal::findOrFail($id);

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
            'sizes',
            'default_size_title',
            'components',
            'addons',
            'macros',
            'production_group_id'
        ]);

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

                $mealSize->title = $size['title'];
                $mealSize->price = $size['price'];
                $mealSize->multiplier = $size['multiplier'];
                $mealSize->save();

                $mealSize->syncIngredients($size['ingredients']);

                $sizeIds->put($size['id'], $mealSize->id);
            }

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
                $mealComponent->minimum = $component['minimum'];
                $mealComponent->maximum = $component['maximum'];
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

                // Deleted component options
                $mealComponent
                    ->options()
                    ->whereNotIn('id', $optionIds)
                    ->delete();
            }

            // Deleted components
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
            $meal
                ->addons()
                ->whereNotIn('id', $addonIds)
                ->delete();
        }

        $meal->update($props->except(['featured_image', 'gallery'])->toArray());

        $macros = $props->get('macros');

        if ($macros && $meal->store->settings->showMacros) {
            $calories = $macros['calories'];
            $carbs = $macros['carbs'];
            $protein = $macros['protein'];
            $fat = $macros['fat'];

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

    public static function deleteMeal($id, $subId = null, $replaceOnly = false)
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
        }

        if (!$replaceOnly) {
            $meal->delete();
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
}
