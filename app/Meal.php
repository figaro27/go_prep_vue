<?php

namespace App;

use App\Store;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Meal extends Model
{

    protected $fillable = [
        'active', 'featured_image', 'title', 'description', 'price', 'created_at',
    ];

    protected $casts = [
        'price' => 'double',
    ];

    protected $appends = ['tag_titles'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function ingredients()
    {
        return $this->hasMany('App\Ingredient');
    }

    public function meal_orders()
    {
        return $this->hasMany('App\MealOrder');
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
        return Meal::with('ingredients', 'meal_tags')->where('id', $id)->first();
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
