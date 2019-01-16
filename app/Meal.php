<?php

namespace App;

use App\Store;
use App\Utils\Data\Format;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Constraint\Exception;

class Meal extends Model
{

    protected $fillable = [
        'active', 'featured_image', 'title', 'description', 'price', 'created_at',
    ];

    protected $casts = [
        'price' => 'double',
        'created_at' => 'date:F d, Y',
    ];

    protected $appends = ['tag_titles', 'quantity', 'nutrition', 'active_orders', 'lifetime_orders', 'allergy_ids', 'category_ids'];

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

    public function getAllergyIdsAttribute()
    {
        return $this->allergies->pluck('id');
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categories->pluck('id');
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
        $id = Auth::user()->id;
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
                $imagePath = 'images/meals/' . self::generateImageFilename($image, $ext[1]);
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
            'title',
            'description',
            'price',
            'created_at',
            'tag_titles',
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
        }

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
                            'unit_type' => Unit::getType('serving_unit'),
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

    public static function deleteMeal($id)
    {
        $meal = Meal::find($id);
        $meal->delete();
    }

    public static function generateImageFilename($image, $ext)
    {
        return sha1($image) . '.' . $ext;
    }

}
