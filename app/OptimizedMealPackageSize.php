<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OptimizedMealPackageSize extends Model
{
    use SoftDeletes;

    protected $table = 'meal_package_sizes';

    public $fillable = [];
    public $casts = [];
}
