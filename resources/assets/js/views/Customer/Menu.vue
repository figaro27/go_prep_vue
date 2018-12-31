<template>
  <div class="container-fluid">
    <div v-for="category in categories" :key="category">
      <button @click="filterByCategory(category)">{{ category }}</button>
    </div>
    <br>
    <div v-for="tag in tags" :key="tag">
      <button @click="filterByTag(tag)">{{ tag }}</button>
    </div>
    <div class="row">
      <div class="col-sm-12 mt-3">
        <div class="card">
          <div class="card-body">
            <b-modal
              ref="mealModal"
              size="lg"
              :title="meal.title"
              v-model="mealModal"
              v-if="mealModal"
            >
              <p>${{ meal.price }}</p>
              <p>{{ meal.description }}</p>
              <img :src="meal.featured_image">
              <img src="/storage/add.jpg" @click="addOne(meal)">
              <p>{{ ingredients }}</p>
              <button @click="getNutritionFacts(meal, meal.ingredients)">Get</button>
              <div id="nutritionFacts"></div>
            </b-modal>
            <div class="row">
              <div class="col-sm-10" style="max-height:800px;overflow-y:auto">
                <div v-for="(meals, category) in meals" :key="category">
                  <h3>{{category}}</h3>
                  <b-row>
                    <b-col v-for="meal in meals" :key="meal.id" cols="3">
                      <img
                        :src="meal.featured_image"
                        class="menu-item-img"
                        @click="showMealModal(meal)"
                      >
                      <img src="/storage/minus.jpg" @click="minusOne(meal)">
                      <b-form-input
                        type="text"
                        name
                        id
                        class="quantity"
                        :value="quantity(meal)"
                        readonly
                      ></b-form-input>
                      <img src="/storage/plus.jpg" @click="addOne(meal)">
                      <p>{{ meal.title }}</p>
                      <p>${{ meal.price }}</p>
                    </b-col>
                  </b-row>
                </div>
              </div>
              <div class="col-sm-2" style="max-height:800px;overflow-y:auto">
                <b-col v-for="(item, mealId) in bag" :key="`bag-${mealId}`" cols="12">
                  <div v-if="item.quantity > 0">
                    <p @click="clearAll">Clear All</p>
                    <img :src="item.meal.featured_image" class="cart-item-img">
                    <p>{{ item.meal.title }}</p>
                    <img src="/storage/minus.jpg" @click="minusOne(item.meal)">
                    {{ item.quantity }}
                    <img
                      src="/storage/plus.jpg"
                      @click="addOne(item.meal)"
                    >
                    <img src="/storage/x.png" @click="clearMeal(item.meal)">
                  </div>
                </b-col>
                <p
                  v-if="total < minimum"
                >Please choose {{ remainingMeals }} {{ singOrPlural }} to continue.</p>
                <hr>
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

<style lang="scss">
@import "~nutrition-label-jquery-plugin/dist/css/nutritionLabel-min.css";

.menu-item {
  margin-bottom: 10px;
}

.menu-item-img {
  width: 100%;
}

.cart-item-img {
  height: 100px;
}

.quantity {
  width: 75px;
  border-radius: 10px;
  opacity: 0.5;
  text-align: center;
}
</style>

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
        tags: []
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
      hasMeal: "bagHasMeal"
    }),
    minimum() {
      return this.store.store_settings ? this.store.store_settings.minimum : 1;
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

          if (filters.categories.length > 0) {
            let hasCat = _.reduce(
              meal.meal_categories,
              (has, cat) => {
                if (has) return true;
                let x = _.includes(filters.categories, cat.category);

                return x;
              },
              false
            );

            skip = skip || !hasCat;
          }

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

          return !skip;
        });
      }

      meals.forEach(meal => {
        meal.meal_categories.forEach(category => {
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
        meal.meal_categories.forEach(category => {
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
    }
  },
  mounted() {},
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
    }
  }
};
</script>
