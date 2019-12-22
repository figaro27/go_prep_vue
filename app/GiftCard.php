<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use App\Media\Utils as MediaUtils;
use Illuminate\Database\Eloquent\SoftDeletes;

class GiftCard extends Model implements HasMedia
{
    use HasMediaTrait;
    use SoftDeletes;

    public $appends = ['category_ids', 'gift_card', 'salesTax', 'image'];

    protected $casts = [
        'created_at' => 'date:F d, Y',
        'created_at_local' => 'date:F d, Y'
    ];

    public function categories()
    {
        return $this->belongsToMany('App\Category')->using(
            'App\GiftCardCategory'
        );
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

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categories->pluck('id');
    }

    public function getGiftCardAttribute()
    {
        return true;
    }

    public function getSalesTaxAttribute()
    {
        return 0;
    }

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
}
