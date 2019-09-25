<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use App\Media\Utils as MediaUtils;

class StoreDetail extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'description',
        'domain'
    ];

    protected $appends = ['logo'];

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            $model->store->clearCaches();
        });
    }

    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('full')
            ->width(1024)
            ->height(1024)
            ->performOnCollections('logo');

        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_MAX, 180, 180)
            ->performOnCollections('logo');

        $this->addMediaConversion('medium')
            ->fit(Manipulations::FIT_MAX, 360, 360)
            ->performOnCollections('logo');
    }

    public function getLogoAttribute()
    {
        return Cache::remember('store_logo_' . $this->store_id, 5, function () {
            if (!$this->hasMedia('logo')) {
                $url = $this->getOriginal('logo');

                return [
                    'url' => $url,
                    'url_thumb' => $url,
                    'url_medium' => $url
                ];
            }

            $logo = $this->getMedia('logo')->first();

            return [
                'id' => $logo->id,
                'url' => $this->store->getUrl(MediaUtils::getMediaPath($logo)),
                'url_thumb' => $this->store->getUrl(
                    MediaUtils::getMediaPath($logo, 'thumb')
                ),
                'url_medium' => $this->store->getUrl(
                    MediaUtils::getMediaPath($logo, 'medium')
                ),
                'path' => MediaUtils::getMediaPath($logo, 'thumb')
            ];
        });
    }

    public function updateLogo($imagePath)
    {
        $fullImagePath = \Storage::disk('public')->path($imagePath);

        $this->clearMediaCollection('logo');
        $this->addMedia($fullImagePath)->toMediaCollection('logo');

        $this->store->clearCaches();
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
