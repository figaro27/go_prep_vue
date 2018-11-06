<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{

    protected $fillable = [
        'featured_image', 'title', 'category', 'description', 'price', 'created_at',
    ];

    public function store(){
		return $this->belongsTo('App\Store');
	}

	public function ingredient(){
		return $this->hasMany('App\Ingredient');
	}

	 public static function getMeals(){
	 	return Meal::all()->map(function($meal){
            return [
            		  "id" => $meal->id,
                      "featured_image" => $meal->featured_image,
                      "title" => $meal->title,
                      "category" => $meal->category,
                      "description" => $meal->description,
                      "price" => $meal->price,
                      "created_at" => $meal->created_at
            ];                         
        });
	 }

	 public static function getMeal($id){
        return Meal::where('id', $id)->first();
    }


}
