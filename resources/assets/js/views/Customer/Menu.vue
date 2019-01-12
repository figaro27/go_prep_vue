<style lang="scss">
@import "~nutrition-label-jquery-plugin/dist/css/nutritionLabel-min.css";

.menu-item {
  margin-bottom: 10px;
}

.menu-item-img {
  width: 100%;
}

.cart-item-img {
  height: 80px;
  width: 80px;
}

.quantity {
  width: 115px;
  border-radius: 20px;
  opacity: 0.5;
  text-align: center;
}

.categories{
  padding-top:20px
}

.menu {
  max-width: 1500px
}

.bag-item{
  border-bottom:1px solid #e1e1e1;
  margin-bottom:10px;
  width:100%;
}

.bag-quantity{
text-align:center;
vertical-align: center;
position:relative;
top:10px;
}

.clear-meal{
  width:10px;
}

li {
  list-style-type: none;
}

</style>

<template>
  <div class="menu container-fluid">
    <div v-if="!willDeliver">
      <b-alert variant="danger" show>You are out of the delivery bounds</b-alert>
    </div>
    <div v-for="category in categories" :key="category">
      <button @click="goToCategory(category)">{{ category }}</button>
    </div>
    <br>
    <div v-for="tag in tags" :key="tag">
      <button @click="filterByTag(tag)">{{ tag }}</button>
    </div>
    <br>
    <div v-for="allergy in allergies" :key="allergy">
      <button @click="filterByAllergy(allergy)">{{ allergy }}</button>
    </div>
    <div class="row">
      <div class="col-sm-12 mt-3">
        <div class="card">
          <div class="card-body">
            <b-modal ref="mealModal" size="lg" :title="meal.title" v-model="mealModal" v-if="mealModal">
              <p>${{ meal.price }}</p>
              <p>{{ meal.description }}</p>
              <img :src="meal.featured_image">
              <img src="/storage/add.jpg" @click="addOne(meal)">
              <p>{{ ingredients }}</p>
              <div id="nutritionFacts" v-if="storeSettings.showNutrition"></div>
            </b-modal>
            <div class="row">
              <div class="col-sm-9" style="max-height:800px;overflow-y:auto">
                <div v-for="(meals, category) in meals" :key="category" :id="category" class="categories">
                  <h2 class="text-center mb-3">{{category}}</h2>
                  <b-row>
                    <b-col v-for="meal in meals" :key="meal.id" cols="3">
                      <img
                        :src="meal.featured_image"
                        class="menu-item-img"
                        @click="showMealModal(meal)"
                      >
                      <div class="d-flex justify-content-between mb-2 mt-1">
                        <img src="/storage/minus.jpg" @click="minusOne(meal)">
                        <b-form-input type="text" name id class="quantity" :value="quantity(meal)" readonly></b-form-input>
                        <img src="/storage/plus.jpg" @click="addOne(meal)">
                      </div>
                      <p>{{ meal.title }}</p>
                      <p>${{ meal.price }}</p>
                      <p v-for="tag in meal.tags">{{ tag.tag }}</p>
                      <hr>
                      <p v-for="allergy in meal.allergies">{{ allergy.title }}</p>
                    </b-col>
                  </b-row>
                </div>
              </div>


              <div class="col-sm-3" style="max-height:800px;overflow-y:auto">
                <p @click="clearAll">Clear All</p>
                <ul class="list-group ">
                  <li v-for="(item, mealId) in bag" :key="`bag-${mealId}`" class="bag-item">
                    
                    <div v-if="item.quantity > 0" class="row">
                      <div class="col-sm-1">
                        <img src="/storage/bag-plus.png" @click="addOne(item.meal)">
                        <p class="bag-quantity">{{ item.quantity }}</p>
                        <img src="/storage/bag-minus.png" @click="minusOne(item.meal)">
                      </div>
                      <div class="col-sm-2">
                        <img :src="item.meal.featured_image" class="cart-item-img"/>
                      </div>
                      <div class="col-sm-5 offset-1">
                        {{ item.meal.title }}
                      </div>
                      <div class="col-sm-2">
                        <img src="/storage/x.png" @click="clearMeal(item.meal)" class="clear-meal">
                      </div>
                    </div>
                  </li>

                </ul>
                <p
                  v-if="total < minimum"
                >Please choose {{ remainingMeals }} {{ singOrPlural }} to continue.</p>
                <div>
                  <router-link to="/customer/bag">
                    <img v-if="total >= minimum" src="/storage/next.jpg" @click="addBagItems(bag)">
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


<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import nutritionFacts from "nutrition-label-jquery-plugin";

export default {
  components: {},
  data() {
    return {
      filteredView: false,
      filters: {
        categories: [],
        tags: [],
        allergies: []
      },
      //bag: {},
      meal: null,
      ingredients: "",
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
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      total: "bagQuantity",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: 'viewedStoreWillDeliver',
    }),
    storeSettings() {
      return this.store.settings;
    },
    minimum() {
      return this.storeSettings.minimum;
    },
    remainingMeals() {
      return this.minimum - this.total;
    },
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "meals";
      }
      return "meal";
    },
    meals() {
      let meals = this.store.meals;
      let filters = this.filters;
      let grouped = {};

      if (this.filteredView) {
        meals = _.filter(meals, meal => {
          let skip = false;

          if (filters.tags.length > 0) {
            let hasTag = _.reduce(
              meal.tags,
              (has, tag) => {
                if (has) return true;
                let x = _.includes(filters.tags, tag.tag);

                return x;
              },
              false
            );

            skip = skip || !hasTag;
          }

          if (filters.allergies.length > 0) {
            let hasAllergy = _.reduce(
              meal.allergies,
              (has, allergy) => {
                if (has) return true;
                let x = _.includes(filters.allergies, allergy.title);

                return x;
              },
              false
            );

            skip = skip || hasAllergy;
          }
          return !skip;
        });
      }

      meals.forEach(meal => {
        meal.categories.forEach(category => {
          if (!_.has(grouped, category.category)) {
            grouped[category.category] = [meal];
          } else {
            grouped[category.category].push(meal);
          }
        });
      });

      return grouped;
    },
    categories() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.categories.forEach(category => {
          if (!_.includes(grouped, category.category)) {
            grouped.push(category.category);
          }
        });
      });
      return grouped;
    },
    tags() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.tags.forEach(tag => {
          if (!_.includes(grouped, tag.tag)) {
            grouped.push(tag.tag);
          }
        });
      });
      return grouped;
    },
    allergies() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.allergies.forEach(allergy => {
          if (!_.includes(grouped, allergy.title)) {
            grouped.push(allergy.title);
          }
        });
      });
      return grouped;
    }
  },
  mounted() {

  },
  methods: {
    quantity(meal) {
      const qty = this.$store.getters.bagItemQuantity(meal);
      return qty;
    },
    addOne(meal) {
      this.$store.commit("addToBag", { meal, quantity: 1 });
    },
    minusOne(meal) {
      this.$store.commit("removeFromBag", { meal, quantity: 1 });
    },
    clearMeal(meal) {
      let quantity = this.quantity(meal);
      this.$store.commit("removeFromBag", { meal, quantity });
    },
    clearAll() {
      this.$store.commit("emptyBag");
    },
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    showMealModal(meal) {
      let self = this;
      let ingredients = meal.ingredients;
      ingredients.forEach(function(ingredient) {
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

      this.$nextTick(() => {
        ingredients.forEach(function(ingredient) {
          self.calories = 0;
          self.totalfat = 0;
          self.satfat = 0;
          self.transfat = 0;
          self.cholesterol = 0;
          self.sodium = 0;
          self.totalcarb = 0;
          self.fibers = 0;
          self.sugars = 0;
          self.proteins = 0;
          self.vitamind = 0;
          self.potassium = 0;
          self.calcium = 0;
          self.iron = 0;
          self.addedsugars = 0;
        });
      });
    },
    getNutritionFacts(meal, ingredients) {
      this.ingredientList = this.getIngredientList(ingredients);
      this.ingredients = this.ingredientList;
      $("#nutritionFacts").nutritionLabel({
        showServingUnitQuantity: false,
        itemName: meal.title,
        ingredientList: this.ingredientList,
        decimalPlacesForQuantityTextbox: 2,
        valueServingUnitQuantity: 1,
        allowFDARounding: true,
        decimalPlacesForNutrition: 2,
        showPolyFat: false,
        showMonoFat: false,
        valueCalories: this.calories,
        valueFatCalories: this.fatcalories,
        valueTotalFat: this.totalfat,
        valueSatFat: this.satfat,
        valueTransFat: this.transfat,
        valueCholesterol: this.cholesterol,
        valueSodium: this.sodium,
        valueTotalCarb: this.totalcarb,
        valueFibers: this.fibers,
        valueSugars: this.sugars,
        valueProteins: this.proteins,
        valueVitaminD: this.vitamind,
        valuePotassium_2018: this.potassium,
        valueCalcium: this.calcium,
        valueIron: this.iron,
        valueAddedSugars: this.addedsugars,
        showLegacyVersion: false
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
      return ingredientList;
    },
    addBagItems(bag) {
      this.$store.commit("addBagItems", bag);
    },
    filterByCategory(category) {
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.categories, cat => {
        return cat === category;
      });

      i === -1
        ? this.filters.categories.push(category)
        : Vue.delete(this.filters.categories, i);
    },
    filterByTag(tag) {
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.tags, _tag => {
        return tag === _tag;
      });

      i === -1
        ? this.filters.tags.push(tag)
        : Vue.delete(this.filters.tags, i);
    },
    filterByAllergy(allergy) {
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.allergies, _allergy => {
        return allergy === _allergy;
      });

      i === -1
        ? this.filters.allergies.push(allergy)
        : Vue.delete(this.filters.allergies, i);
    },
    goToCategory(category){
      window.location.href = '#' + category;
    }
  }
};
</script>
