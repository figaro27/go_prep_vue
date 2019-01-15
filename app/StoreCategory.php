<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{

    protected $fillable = [
      'user_id',
      'category',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public $appends = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    protected $casts = [
       
    ];

}
