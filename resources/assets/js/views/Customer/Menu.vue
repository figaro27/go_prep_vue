<template>
  <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 mt-3">
                <div class="card">
                    <div class="card-body">
                      <b-modal size="lg" :title="meal.title" v-model="mealModal" v-if="mealModal">
                        <p>${{ meal.price }}</p>
                        <p> {{ meal.description }}</p>
                        <img :src="meal.featured_image">
                        <img src="/storage/add.jpg" @click="addOne(meal)">
                        <p>{{ ingredients }}</p>
                        <button @click="getNutritionFacts(meal, meal.ingredients)">Get</button>
                      <div id="nutritionFacts"></div>
                      </b-modal>
                        <div class="row">
                            <div class="col-sm-10" style="max-height:800px;overflow-y:auto">
                                <b-row>
                                  <b-col v-for="meal in store.meals" :key="meal.id" cols="3">
                                        <img :src="meal.featured_image" class="menu-item-img" @click="showMealModal(meal)">
                                        <img src="/storage/minus.jpg" @click="minusOne(meal)">
                                        <b-form-input type="text" name="" id="" class="quantity" v-model="meal.quantity" readonly></b-form-input>
                                        <img src="/storage/plus.jpg" @click="addOne(meal)">
                                        <p> {{ meal.title }} </p>
                                        <p> ${{ meal.price }} </p>
                                  </b-col>
                                </b-row>
                              </div>
                              <div class="col-sm-2" style="max-height:800px;overflow-y:auto">

                                <b-col v-for="meal in store.meals" :key="meal.id" cols="12">
                                  <div v-if="meal.quantity > 0">
                                    <p @click="clearAll">Clear All</p>
                                    <img :src="meal.featured_image" class="cart-item-img">
                                    <p> {{ meal.title }} </p>
                                    <img src="/storage/minus.jpg" @click="minusOne(meal)">
                                    {{ meal.quantity }}
                                    <img src="/storage/plus.jpg" @click="addOne(meal)">
                                    <img src="/storage/x.png" @click="clearMeal(meal)">
                                  </div>
                                </b-col>
                                <p v-if="total < minimum">Please choose {{ remainingMeals }} {{ singOrPlural }} to continue.</p>
                                <hr>
                                <div>
                                <router-link to="/customer/bag">
                                  <img v-if="total >= minimum" src="/storage/next.jpg">
                                </router-link>
                                </div>
                                
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
</template>

<style lang="scss">
@import "~nutrition-label-jquery-plugin/dist/css/nutritionLabel-min.css";

.menu-item{
    margin-bottom:10px;
}

.menu-item-img{
    width:100%;
}

.cart-item-img{
  height:100px;
}

.quantity{
  width:75px;
  border-radius:10px;
  opacity:.5;
  text-align:center;
}



</style>

<script>
import { mapGetters } from "vuex";
import nutritionFacts from "nutrition-label-jquery-plugin";

    export default {
        components: {
        },
        data(){
            return {
                groupedByCategory: {},
                meals: [],
                meal: null,
                total: 0,
                ingredients: '',
                mealModal: false,
                  calories: null,
                  totalfat: null,
                  satfat: null,
                  transfat: null,
                  cholesterol: null,
                  sodium: null,
                  totalcarb: null,
                  fibers: null,
                  sugars: null,
                  proteins: null,
                  vitamind: null,
                  potassium: null,
                  calcium: null,
                  iron: null,
                  addedsugars: null
            }
        },
        computed: {
          ...mapGetters({
            store: "viewedStore"
          }),
          minimum(){
            return this.store.store_settings.minimum;
          },
          remainingMeals(){
            return this.minimum - this.total;
          },
          singOrPlural(){
            if (this.remainingMeals > 1){
              return "meals";
            }
              return "meal";
          } 
        },
        mounted()
        {
          this.groupByCategory();
        },
        methods: {
            groupByCategory(){
              let meals = this.store.meals;
              this.groupedByCategory = _.groupBy(meals, "meal_categories")
            },
            minusOne(meal){
                meal.quantity -= 1;
                if (meal.quantity < 0){
                    meal.quantity += 1;
                }
                this.total -= 1;
                this.preventNegative();
            },
            addOne(meal){
                meal.quantity += 1;
                this.total +=1;
                this.preventNegative();
                this.mealModal = false;
            },
            clearMeal(meal){
              this.total -= meal.quantity;
              this.preventNegative();
              meal.quantity = 0;
            },
            clearAll(){
              this.store.meals.forEach(function(meal){
                meal.quantity = 0;
              });
              this.total = 0;
              this.preventNegative();
          },
            preventNegative(){
              if (this.total < 0){
                this.total += 1;
              }
            },
            showMealModal(meal){
                let self = this;
                let ingredients = meal.ingredients;
                ingredients.forEach(function(ingredient){
                  self.calories += ingredient.calories;
                  self.totalfat += ingredient.totalfat;
                  self.satfat += ingredient.satfat;
                  self.transfat += ingredient.transfat;
                  self.cholesterol += ingredient.cholesterol;
                  self.sodium += ingredient.sodium;
                  self.totalcarb += ingredient.totalcarb;
                  self.fibers += ingredient.fibers;
                  self.sugars += ingredient.sugars;
                  self.proteins += ingredient.proteins;
                  self.vitamind += ingredient.vitamind;
                  self.potassium += ingredient.potassium;
                  self.calcium += ingredient.calcium;
                  self.iron += ingredient.iron;
                  self.addedsugars += ingredient.addedsugars;
                });
              this.meal = meal;
              this.mealModal = true;

              this.$nextTick(() => {
                this.getNutritionFacts(this.meal, this.meal.ingredients);
              });
            },
            getNutritionFacts(meal, ingredients) {
              this.ingredientList = this.getIngredientList(ingredients);
              this.ingredients = this.ingredientList;
              $('#nutritionFacts').nutritionLabel({
                showServingUnitQuantity : false,
                itemName : meal.title,
                ingredientList : this.ingredientList,
                decimalPlacesForQuantityTextbox : 2,
                valueServingUnitQuantity : 1,
                allowFDARounding : true,
                decimalPlacesForNutrition : 2,
                showPolyFat : false,
                showMonoFat : false,
                valueCalories : this.calories,
                valueFatCalories : this.fatcalories,
                valueTotalFat : this.totalfat,
                valueSatFat : this.satfat,
                valueTransFat : this.transfat,
                valueCholesterol : this.cholesterol,
                valueSodium : this.sodium,
                valueTotalCarb : this.totalcarb,
                valueFibers : this.fibers,
                valueSugars : this.sugars,
                valueProteins : this.proteins,
                valueVitaminD : this.vitamind,
                valuePotassium_2018 : this.potassium,
                valueCalcium : this.calcium,
                valueIron : this.iron,
                valueAddedSugars : this.addedsugars,
                showLegacyVersion : false
             });
            },
            getIngredientList: function(ingredients) {
              let ingredientList = "";
              ingredients.forEach(function(ingredient) {
                ingredientList +=
                  ingredient.food_name.charAt(0).toUpperCase() +
                  ingredient.food_name.slice(1) +
                  ", ";
              });
              return ingredientList
            },
        }
    }
</script>
