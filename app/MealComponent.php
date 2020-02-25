<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\MealSubscriptionComponent;

class MealComponent extends Model
{
    use SoftDeletes;

    protected $table = 'meal_components';

    protected $fillable = [];

    protected $casts = [
        'price' => 'double',
        'created_at' => 'date:F d, Y'
    ];

    protected $appends = ['activeSubscriptions'];

    protected $hidden = [];

    protected $with = ['options'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at'];

    public function meal()
    {
        return $this->belongsTo('meal');
    }

    public function store()
    {
        return $this->belongsTo('store');
    }

    public function options()
    {
        return $this->hasMany('App\MealComponentOption');
    }

    public function getActiveSubscriptionsAttribute()
    {
        $mealSubs = MealSubscriptionComponent::where(
            'meal_component_id',
            $this->id
        )
            ->whereHas('mealSubscription', function ($mealSub) {
                $mealSub->whereHas('subscription', function ($sub) {
                    $sub->where('status', '=', 'active');
                });
            })
            ->count();

        if ($mealSubs > 0) {
            return true;
        } else {
            return false;
        }
    }
}
