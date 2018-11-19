<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Store;


class Meal extends Model
{

  protected $fillable = [
      'active', 'featured_image', 'title', 'description', 'price', 'created_at',
  ];

  public function store(){
		return $this->belongsTo('App\Store');
	}

	public function ingredient(){
		return $this->hasMany('App\Ingredient');
	}

  public function meal_order(){
    return $this->hasMany('App\MealOrder');
  }

  public function meal_tag(){
    return $this->hasMany('App\MealTag');
  }

	 
  //Admin View
  public static function getMeals(){

    return Meal::orderBy('created_at', 'desc')->get()->map(function($meal){
            return [
                  "id" => $meal->id,
                  "active" => $meal->active,
                  "featured_image" => $meal->featured_image,
                  "title" => $meal->title,
                  "description" => $meal->description,
                  "price" => $meal->price,
                  "created_at" => $meal->created_at
            ];                         
        });
   }

  //Store View
  public static function getStoreMeals(){

  $id = Auth::user()->id;
  $storeID = Store::where('user_id', $id)->pluck('id')->first();

 	return Meal::with('meal_order', 'ingredient', 'meal_tag')->where('store_id', $storeID)->orderBy('active', 'desc')->orderBy('created_at', 'desc')->get()->map(function($meal){
    // Fix redundancy of getting the authenticated Store ID twice
    $id = Auth::user()->id;
    $storeID = Store::where('user_id', $id)->pluck('id')->first();
          return [
          		  "id" => $meal->id,
                "active" => $meal->active,
                "featured_image" => $meal->featured_image,
                "title" => $meal->title,
                "description" => $meal->description,
                "price" => '$'.$meal->price,
                "current_orders" => $meal->meal_order->where('store_id', $storeID)->count(),
                "created_at" => $meal->created_at,
                'ingredients' => $meal->ingredient,
                'meal_tags' => $meal->meal_tag
            ];                         
        });
	 }

 public static function getMeal($id){
      return Meal::with('ingredient', 'meal_tag')->where('id', $id)->first();
  }

  //Considering renaming "Store" to "Company" to not cause confusion with store methods.
  public static function storeMeal($request){
    $id = Auth::user()->id;
    $storeID = Store::where('user_id', $id)->pluck('id')->first();
      $meal = new Meal;
      $meal->active = true;
      $meal->store_id = $storeID;
      $meal->featured_image = $request->featured_image;
      $meal->title = $request->title;
      $meal->description = $request->description;
      $meal->price = $request->price;

      $meal->save();
  }

  public static function storeMealAdmin($request){
      $meal = new Meal;
      $meal->active = true;
      $meal->store_id = $request->store_id;
      $meal->featured_image = $request->featured_image;
      $meal->title = $request->title;
      $meal->description = $request->description;
      $meal->price = $request->price;

      $meal->save();
  }

  public static function updateMeal($request, $id){
      $meal = Meal::where('id', $id)->first();
     
      $meal->update([
          'active' => $request->meal['active'],
          'featured_image' => $request->meal['featured_image'],
          'title' => $request->meal['title'],
          'description' => $request->meal['description'],
          'price' => $request->meal['price'],
          'created_at' => $request->meal['created_at'],
      ]);
  }

  public static function updateActive($request){
    $meal = Meal::where('id', $request->id)->first();

    $meal->update([
      'active' => $request->active
    ]);
  }

  public static function deleteMeal($id){
      $meal = Meal::find($id);
      $meal->delete();
  }

}
