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
use DB;

class OptimizedMeal extends Model implements HasMedia
{
    use SoftDeletes;
    use LocalizesDates;
    use HasMediaTrait;

    protected $table = 'meals';

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

    protected $appends = ['image', 'category_ids', 'tag_titles', 'allergy_ids'];

    protected $hidden = [
        'allergies',
        'categories',
        'orders',
        'subscriptions',
        'store'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'local_created_at'];

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

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function sizes()
    {
        return $this->hasMany('App\MealSize', 'meal_id', 'id');
    }

    public function macros()
    {
        return $this->hasOne('App\MealMacro', 'meal_id', 'id');
    }

    public function getCategoryIdsAttribute()
    {
        $meal_id = $this->id;
        $categories = DB::select(
            DB::raw(
                "select category_id from category_meal where meal_id=$meal_id"
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

    public function tags()
    {
        return $this->belongsToMany(
            'App\MealTag',
            'meal_meal_tag',
            'meal_id',
            'id'
        );
    }

    public function getTagTitlesAttribute()
    {
        return collect($this->tags)->map(function ($meal) {
            return $meal->tag;
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

    public function getAllergyIdsAttribute()
    {
        return $this->allergies->pluck('id');
    }
}
