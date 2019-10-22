<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use DB;

class OptimizedMealPackage extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $table = 'meal_packages';

    public $fillable = [
        'title',
        'description',
        'store_id',
        'price',
        'active',
        'default_size_title',
        'meal_carousel'
    ];

    public $appends = ['image', 'category_ids'];
    public $hidden = ['store', 'categories'];

    protected $casts = [
        'price' => 'double',
        'active_orders_price' => 'decimal:2',
        'created_at' => 'date:F d, Y',
        'created_at_local' => 'date:F d, Y',
        'meal_carousel' => 'boolean'
    ];

    public function meals()
    {
        return $this->belongsToMany('App\\Meal')
            ->withPivot(['meal_size_id', 'quantity'])
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

    public function getCategoryIdsAttribute()
    {
        $meal_package_id = $this->id;
        $categories = DB::select(
            DB::raw(
                "select category_id from category_meal_package where meal_package_id=$meal_package_id"
            )
        );

        $data = [];
        if ($categories) {
            foreach ($categories as $category) {
                $category_id = $category->category_id;

                if (!in_array($category_id, $data)) {
                    $data[] = $category_id;
                }
            }
        }

        return $data;
    }

    public function addons()
    {
        return $this->hasMany('App\MealPackageAddon', 'meal_package_id', 'id');
    }

    public function getImageAttribute()
    {
        //$mediaItems = $this->getMedia('featured_image');
        $mediaItems = Media::where([
            'collection_name' => 'featured_image',
            'model_type' => 'App\MealPackage',
            'model_id' => $this->id
        ])->get();

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
}
