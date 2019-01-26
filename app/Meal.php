<?php

namespace App;

use App\Store;
use App\Utils\Data\Format;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Constraint\Exception;

class Meal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'active', 'featured_image', 'title', 'description', 'price', 'created_at',
    ];

    protected $casts = [
        'price' => 'double',
        'active_orders_price' => 'decimal:2',
        'created_at' => 'date:F d, Y',
    ];

    protected $appends = [
      'tag_titles',
      'nutrition',
      'active_orders',
      'active_orders_price',
      'lifetime_orders',
      'allergy_ids',
      'category_ids',
      'tag_ids',
      'substitute_ids',
      'ingredient_ids',
      'order_ids',
    ];

    protected $hidden = [
      'allergies',
      'categories',
      'orders',
      //'ingredients',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function getQuantityAttribute()
    {
        return 0;
    }

    public function getLifetimeOrdersAttribute()
    {
        return $this->orders->count();
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

                $nutrition[$field] += $ingredient[$field] * $ingredient->pivot->quantity_grams;
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

    public function getSubstituteIdsAttribute()
    {
        $ids = Cache::rememberForever('meal_substitutes_' . $this->id, function () {
            $mealsQuery = $this->store->meals()->where([
                ['id', '<>', $this->id],
                ['price', '<=', $this->price * 1.2],
                ['price', '>=', $this->price * 0.8],
            ])
                ->whereDoesntHave('allergies', function ($query) {
                    $allergyIds = $this->allergies->pluck('id');
                    $query->whereIn('allergies.id', $allergyIds);
                });

            $meals = $mealsQuery->get();
            if ($meals->count() <= 5) {
                return $meals->pluck('id');
            }

            $mealsQuery = $mealsQuery->whereNotIn('id', $meals->pluck('id'));

            $mealsQuery = $mealsQuery->whereHas('categories', function ($query) {
                $catIds = $this->categories->pluck('id');
                return $query->whereIn('categories.id', $catIds);
            }, '>=', 1)
            ->orWhereHas('tags', function ($query) {
                $tagIds = $this->tags->pluck('id');
                return $query->whereIn('meal_tags.id', $tagIds);
            }, '>=', 1);

            $extraMeals = $mealsQuery->get();

            return $meals->concat($extraMeals)->slice(0, 5)->pluck('id');
        });

        return $ids;

    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function ingredients()
    {
        return $this->belongsToMany('App\Ingredient')->withPivot('quantity', 'quantity_unit')->using('App\IngredientMeal');
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

    public function tags()
    {
        return $this->belongsToMany('App\MealTag', 'meal_meal_tag');
    }

    public function getTagTitlesAttribute()
    {
        return collect($this->tags)->map(function ($meal) {
            return $meal->tag;
        });
    }

    public function allergies()
    {
        return $this->belongsToMany('App\Allergy', 'allergy_meal', 'meal_id', 'allergy_id')->using('App\MealAllergy');
    }

    //Admin View
    public static function getMeals()
    {

        return Meal::orderBy('created_at', 'desc')->get()->map(function ($meal) {
            return [
                "id" => $meal->id,
                "active" => $meal->active,
                "featured_image" => $meal->featured_image,
                "title" => $meal->title,
                "description" => $meal->description,
                "price" => $meal->price,
                "created_at" => $meal->created_at,
            ];
        });
    }

    //Store View
    public static function getStoreMeals($storeID = null)
    {

        return Meal::with('meal_order', 'ingredients', 'meal_tags')->where('store_id', $storeID)->orderBy('active', 'desc')->orderBy('created_at', 'desc')->get()->map(function ($meal) {
            // Fix redundancy of getting the authenticated Store ID twice
            $id = Auth::user()->id;
            $storeID = Store::where('user_id', $id)->pluck('id')->first();
            return [
                "id" => $meal->id,
                "active" => $meal->active,
                "featured_image" => $meal->featured_image,
                "title" => $meal->title,
                "description" => $meal->description,
                "price" => '$' . $meal->price,
                "current_orders" => $meal->meal_order->where('store_id', $storeID)->count(),
                "created_at" => $meal->created_at,
                'ingredients' => $meal->ingredients,
                'meal_tags' => $meal->meal_tags,
            ];
        });
    }

    public static function getMeal($id)
    {
        return Meal::with('ingredients', 'tags', 'categories')->where('id', $id)->first();
    }

    //Considering renaming "Store" to "Company" to not cause confusion with store methods.
    public static function storeMeal($request)
    {
        $id = auth('api')->user()->id;
        $storeID = Store::where('user_id', $id)->pluck('id')->first();
        $meal = new Meal;
        $meal->active = true;
        $meal->store_id = $storeID;
        $meal->featured_image = '';
        $meal->title = $request->title;
        $meal->description = $request->description;
        $meal->price = $request->price;

        if ($request->has('featured_image')) {
            $imageRaw = $request->get('featured_image');
            $imageRaw = str_replace(' ', '+', $imageRaw);
            $image = base64_decode($imageRaw);

            $ext = [];
            preg_match('/^data:image\/(.{3,9});/i', $imageRaw, $ext);

            if (count($ext) > 1) {
                $imagePath = 'public/images/meals/' . self::generateImageFilename($image, $ext[1]);
                \Storage::put($imagePath, $image);
                $imageUrl = \Storage::url($imagePath);

                $meal->featured_image = $imagePath;
            }
        }

        $meal->save();

        $tagTitles = $request->get('tag_titles');
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
        }

    }

    public static function storeMealAdmin($request)
    {
        $meal = new Meal;
        $meal->active = true;
        $meal->store_id = $request->store_id;
        $meal->featured_image = $request->featured_image;
        $meal->title = $request->title;
        $meal->description = $request->description;
        $meal->price = $request->price;

        $meal->save();
    }

    public static function updateMeal($id, $props)
    {

        $meal = Meal::findOrFail($id);

        $props = collect($props)->only([
            'active',
            'featured_image',
            'photo',
            'title',
            'description',
            'price',
            'created_at',
            'tags',
            'categories',
            'ingredients',
            'allergies',
        ]);

        if ($props->has('featured_image')) {
            $imageRaw = $props->get('featured_image');
            $imageRaw = str_replace(' ', '+', $imageRaw);
            $image = base64_decode($imageRaw);

            $ext = [];
            preg_match('/^data:image\/(.{3,9});/i', $imageRaw, $ext);

            if (count($ext) > 1) {
                $imagePath = 'images/meals/' . self::generateImageFilename($image, $ext[1]);
                \Storage::put($imagePath, $image);
                $imageUrl = \Storage::url($imagePath);

                $props->put('featured_image', $imagePath);
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
                    if (is_numeric($newIngredient) || isset($newIngredient['id'])) {
                        $ingredientId = is_numeric($newIngredient) ? $newIngredient : $newIngredient['id'];
                        $ingredient = Ingredient::where('store_id', $meal->store_id)->findOrFail($ingredientId);
                        $ingredients->push($ingredient);
                    } else {
                        // Check if ingredient with same name and unit type already exists
                        $ingredient = Ingredient::where([
                            'store_id' => $meal->store_id,
                            'food_name' => $newIngredient['food_name'],
                            'unit_type' => Unit::getType($newIngredient['serving_unit']),
                        ])->first();

                        if ($ingredient) {
                            $ingredients->push($ingredient);
                        }
                        // Nope. Create a new one
                        else {
                            $newIngredient = collect($newIngredient)->only([
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
                            ])->map(function ($val) {
                                return is_null($val) ? 0 : $val;
                            });

                            $ingredientArr = Ingredient::normalize($newIngredient->toArray());
                            $ingredient = new Ingredient($ingredientArr);
                            $ingredient->store_id = $meal->store_id;
                            if ($ingredient->save()) {
                                $ingredients->push($ingredient);

                                $meal->store->units()->create([
                                    'store_id' => $meal->store_id,
                                    'ingredient_id' => $ingredient->id,
                                    'unit' => Format::baseUnit($ingredient->unit_type),
                                ]);

                            } else {
                                throw new \Exception('Failed to create ingredient');
                            }
                        }
                    }
                } catch (\Exception $e) {
                    die($e);
                }
            }

            $syncIngredients = $ingredients->mapWithKeys(function ($val, $key) use ($newIngredients) {
                return [$val->id => [
                    'quantity' => $newIngredients[$key]['quantity'] ?? 1,
                    'quantity_unit' => $newIngredients[$key]['quantity_unit'] ?? Format::baseUnit($val->unit_type),
                ]];
            });

            $meal->ingredients()->sync($syncIngredients);
        }

        $allergies = $props->get('allergies');
        if (is_array($allergies)) {
            $allergyIds = array_map(function ($allergy) {
                return is_numeric($allergy) ? $allergy : $allergy->id;
            }, $allergies);

            $meal->allergies()->sync($allergyIds);
        }

        $categories = $props->get('categories');
        if (is_array($categories)) {
            $categoryIds = array_map(function ($category) {
                return is_numeric($category) ? $category : $category->id;
            }, $categories);

            $meal->categories()->sync($categoryIds);
        }

        $tags = $props->get('tags');
        if (is_array($tags)) {
            $tagIds = array_map(function ($tag) {
                return is_numeric($tag) ? $tag : $tag->id;
            }, $tags);

            $meal->tags()->sync($tagIds);
        }

        $meal->update($props->toArray());

        return $meal;
    }

    public static function updateActive($id, $active)
    {
        $meal = Meal::where('id', $id)->first();

        $meal->update([
            'active' => $active,
        ]);
    }

    public static function deleteMeal($id, $subId)
    {
        $meal = Meal::find($id);
        $sub = Meal::find($subId);

        $mealOrders = MealOrder::with(['order'])
            ->where([
                'meal_id' => $meal->id,
            ])
            ->get()
            ->filter(function ($mealOrder) {
                return $mealOrder->order->subscription_id > 0;
            });

        $mealOrders->each(function ($mealOrder) use ($sub) {
            $mealOrder->update(['meal_id' => $sub->id]);
        });

        $meal->delete();
    }

    public static function generateImageFilename($image, $ext)
    {
        return sha1($image) . '.' . $ext;
    }

}
