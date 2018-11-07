<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Store;


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

    return Meal::orderBy('created_at', 'desc')->get()->map(function($meal){
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

    public static function getStoreMeals(){

    $id = Auth::user()->id;
    $storeID = Store::where('user_id', $id)->pluck('id')->first();

	 	return Meal::where('store_id', $storeID)->orderBy('created_at', 'desc')->get()->map(function($meal){
            return [
            		  "id" => $meal->id,
                  "featured_image" => $meal->featured_image,
                  "title" => $meal->title,
                  "category" => $meal->category,
                  "description" => $meal->description,
                  "price" => '$'.$meal->price,
                  "created_at" => $meal->created_at
            ];                         
        });
	 }

	 public static function getMeal($id){
        return Meal::where('id', $id)->first();
    }

    public static function storeMeal($request){
      $id = Auth::user()->id;
      $storeID = Store::where('user_id', $id)->pluck('id')->first();


        $meal = new Meal;
        $meal->store_id = $storeID;
        $meal->featured_image = $request->featured_image;
        $meal->title = $request->title;
        $meal->category = $request->category;
        $meal->description = $request->description;
        $meal->price = $request->price;

        $meal->save();
    }

    public static function storeMealAdmin($request){
        $meal = new Meal;
        $meal->store_id = $request->store_id;
        $meal->featured_image = $request->featured_image;
        $meal->title = $request->title;
        $meal->category = $request->category;
        $meal->description = $request->description;
        $meal->price = $request->price;

        $meal->save();
    }

    public static function updateMeal($request, $id){
        $meal = Meal::where('id', $id)->first();
       
        $meal->update([
            'featured_image' => $request->meal['featured_image'],
            'title' => $request->meal['title'],
            'category' => $request->meal['category'],
            'description' => $request->meal['description'],
            'price' => $request->meal['price'],
            'created_at' => $request->meal['created_at'],
        ]);
    }

    public static function deleteMeal($id){
        $meal = Meal::find($id);
        $meal->delete();
    }

}
