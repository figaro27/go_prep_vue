<template>
  <div>
    <div v-if="!storeSettings.open">
      <div class="row">
        <div class="col-sm-12 mt-3">
          <div class="card">
            <div class="card-body">
              <h5 class="center-text">This company will not be taking new orders at this time.</h5>
              <p class="center-text mt-3"><strong>Reason:</strong> {{ storeSettings.closedReason }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  <div class="menu container-fluid" v-if="storeSettings.open">
    <div v-if="!willDeliver && !preview && loggedIn">
      <b-alert variant="danger center-text" show>You are outside of the delivery area.</b-alert>
    </div>

    <div class="modal-basic">
      <b-modal size="lg" v-model="viewFilterModal" v-if="viewFilterModal" hide-header>
        <div>
          <h4 class="center-text mb-5 mt-5">Hide Meals That Contain</h4>
        </div>
        <div class="row mb-4">
          <div v-for="allergy in allergies" :key="`allergy-${allergy.id}`" class="filters col-md-3 mb-3">
            <b-button
              :pressed="active[allergy.id]"
              @click="filterByAllergy(allergy.id)"
            >{{ allergy.title }}</b-button>
          </div>
        </div>
        <hr>
        <div>
          <h4 class="center-text mb-5">Show Meals With</h4>
        </div>
        <div class="row">
          <div v-for="tag in tags" :key="`tag-${tag}`" class="filters col-md-3 mb-3">
            <b-button :pressed="active[tag]" @click="filterByTag(tag)">{{ tag }}</b-button>
          </div>
        </div>
        <b-button @click="clearFilters" variant="primary" class="center mt-4">Clear All</b-button>
      </b-modal>
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
              <div class="row mt-3">
                <div class="col-md-6 modal-meal-image">
                  <img :src="meal.featured_image">
                  <p v-if="storeSettings.showNutrition">{{ meal.description }}</p>
                  <div class="row mt-3 mb-5" v-if="storeSettings.showNutrition">
                    <div class="col-md-6">
                      <h5>Tags</h5>
                      <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                    </div>
                    <div class="col-md-6">
                      <h5>Contains</h5>
                      <li v-for="allergy in meal.allergy_titles">{{ allergy }}</li>
                    </div>
                  </div>

                  <div class="row mt-5" v-if="storeSettings.showNutrition">
                    <div class="col-md-5 mt-3">
                      <h5>{{ format.money(meal.price) }}</h5>
                    </div>
                    <div class="col-md-7">
                      <b-btn @click="addOne(meal)" class="menu-bag-btn">+ ADD</b-btn>
                    </div>
                  </div>
                </div>
                <div class="col-md-6" v-if="storeSettings.showNutrition">
                  <div id="nutritionFacts"></div>
                </div>
                <div class="col-md-6" v-if="!storeSettings.showNutrition">
                  <p>{{ meal.description }}</p>
                  <div class="row">
                    <div class="col-md-6">
                      <h5>Tags</h5>
                      <li v-for="tag in meal.tags">{{ tag.tag }}</li>
                    </div>
                    <div class="col-md-6">
                      <h5>Contains</h5>
                      <li v-for="allergy in meal.allergy_titles">{{ allergy }}</li>
                    </div>
                  </div>
                  <div class="row mt-3 mb-3">
                    <div class="col-md-12">
                      <h5>Ingredients</h5>
                      {{ ingredients }}
                    </div>
                  </div>
                  <div class="row" v-if="storeSettings.showNutrition">
                    <div class="col-md-8">
                      <h5>{{ format.money(meal.price) }}</h5>
                    </div>
                    <div class="col-md-4">
                      <img src="/images/customer/add.jpg" @click="addOne(meal)">
                    </div>
                  </div>
                  <div class="row" v-if="!storeSettings.showNutrition">
                    <div class="col-md-6">
                      <h5>{{ format.money(meal.price) }}</h5>
                    </div>
                    <div class="col-md-6">
                      <img src="/images/customer/add.jpg" @click="addOne(meal)">
                    </div>
                  </div>
                </div>
              </div>

              <!-- <div class="row" v-if="!showNutrition">
              <div class="col-md-12 modal-meal-image">
                <img :src="meal.featured_image">
                <p>{{ meal.description }}</p>
                  <div class="row">
                    <div class="col-md-8">
                      <h5>{{ format.money(meal.price) }}</h5>
                    </div>
                    <div class="col-md-4">
                      <img src="/images/customer/add.jpg" @click="addOne(meal)">
                    </div>
                  </div>
              </div>
              </div>-->
            </b-modal>

            <div class="row">
              <div class="col-sm-9 col-md-9 order-2 order-sm-1">

                <div class="row">
                  <div class="col-sm-12 store-logo-area">
                    <img v-if="storeLogo" class="store-logo" :src="storeLogo" alt="Company Logo"/>
                  </div>
                  <div class="col-sm-12 category-area">
                      <ul v-for="category in categories" :key="category">
                        <li @click="goToCategory(category)">{{ category }}</li>
                      </ul>
                  </div>
                  </div>
                

                <div class="filter-area">
                  <b-button @click="viewFilters" variant="primary" class=""><i class="fa fa-filter"></i>&nbsp;Filters</b-button>
                  <b-button @click="clearFilters" variant="warning" class=""><i class="fa fa-eraser"></i>&nbsp;Clear Filters</b-button>
                </div>
              </div>
              <div class="col-sm-3 col-md-3 order-1 order-sm-2">
                <b-btn variant="danger" class="pull-right" @click="clearAll">Clear All</b-btn>
              </div>
            </div>
            <div class="row">
              <div :class="`col-md-9 order-2 order-sm-1  main-menu-area`">
                <Spinner v-if="!meals.length" position="absolute"/>
                <div
                  v-for="group in meals"
                  :key="group.category"
                  :id="group.category"
                  class="categories"
                >
                  <h2 class="text-center mb-3 dbl-underline">{{group.category}}</h2>
                  <div class="row">
                    <div
                      class="col-sm-6 col-lg-4 col-xl-3"
                      v-for="meal in group.meals"
                      :key="meal.id"
                    >
                      <thumbnail
                        :src="meal.featured_image"
                        
                        class="menu-item-img"
                        @click="showMealModal(meal)"></thumbnail>
                      <div class="d-flex justify-content-between mb-2 mt-1">
                        <b-btn @click="minusOne(meal)" class="menu-bag-btn plus-minus gray">
                          <p>-</p>
                        </b-btn>
                        <!-- <img src="/images/customer/minus.jpg" @click="minusOne(meal)" class="plus-minus"> -->
                        <b-form-input
                          type="text"
                          name
                          id
                          class="quantity"
                          :value="quantity(meal)"
                          readonly
                        ></b-form-input>
                        <b-btn @click="addOne(meal)" class="menu-bag-btn plus-minus">
                          <p>+</p>
                        </b-btn>
                        <!-- <img src="/images/customer/plus.jpg" @click="addOne(meal)" class="plus-minus"> -->
                      </div>
                      <p class="center-text strong featured">{{ meal.title }}</p>
                      <p class="center-text featured">{{ format.money(meal.price) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-5 col-md-3 order-1 order-sm-2 bag-area">
                <ul class="list-group">
                  <li v-for="(item, mealId) in bag" :key="`bag-${mealId}`" class="bag-item">
                    <div v-if="item && item.quantity > 0" class="d-flex align-items-center">
                      <div class="mr-2">
                        <!-- <p @click="addOne(item.meal)" class="bag-plus">+</p> -->
                        <img
                          src="/images/customer/bag-plus.png"
                          @click="addOne(item.meal)"
                          class="bag-plus-minus"
                        >
                        <p class="bag-quantity">{{ item.quantity }}</p>
                        <!-- <p @click="minusOne(item.meal)" class="bag-minus">-</p> -->
                        <img
                          src="/images/customer/bag-minus.png"
                          @click="minusOne(item.meal)"
                          class="bag-plus-minus"
                        >
                      </div>
                      <div class="bag-item-image mr-2">
                        <thumbnail :src="item.meal.featured_image" :src-placeholder="item.meal.featured_image" class="cart-item-img"></thumbnail>
                      </div>
                      <div class="flex-grow-1 mr-2">{{ item.meal.title }}</div>
                      <div class>
                        <img
                          src="/images/customer/x.png"
                          @click="clearMeal(item.meal)"
                          class="clear-meal"
                        >
                      </div>
                    </div>
                  </li>
                </ul>
                <p class="center-text" v-if="minOption === 'meals' && total < minimumMeals">
                  Please add {{ remainingMeals }} {{ singOrPlural }} to continue.
                </p>
                <div>
                  <router-link to="/customer/bag">
                    <b-btn v-if="minOption === 'meals' && total >= minimumMeals && !preview" class="menu-bag-btn">NEXT</b-btn>
                  </router-link>
                </div>

                <p class="pull-right" v-if="minOption === 'price' && totalBagPrice < minPrice">
                  Please add {{format.money(remainingPrice)}} more to continue.
                </p>
                <div>
                  <router-link to="/customer/bag">
                    <b-btn v-if="minOption === 'price' && totalBagPrice >= minPrice && !preview" class="menu-bag-btn">NEXT</b-btn>
                  </router-link>
                </div>
                  <h6 class="mt-2 pull-right">SubTotal - ${{ totalBagPrice }}</h6>
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
import Spinner from "../../components/Spinner";

window.addEventListener("hashchange", function() {
  window.scrollTo(window.scrollX, window.scrollY - 500);
});

export default {
  components: {
    Spinner
  },
  props: {
    preview: {
      default: false
    }
  },
  data() {
    return {
      active: {},
      viewFilterModal: false,
      filteredView: false,
      filters: {
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
      allergies: "allergies",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: "viewedStoreWillDeliver",
      _categories: "viewedStoreCategories",
      storeLogo: "viewedStoreLogo",
      isLoading: "isLoading",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: 'minimumPrice'
    }),
    storeSettings() {
      return this.store.settings;
    },
    minimumOption() {
      return this.minOption;
    },
    minimumMeals(){
      return this.minMeals;
    },
    minimumPrice(){
      return this.minPrice;
    },
    remainingMeals() {
      return this.minMeals - this.total;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPrice;
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

      if (!_.isArray(meals)) {
        return [];
      }

      meals = _.filter(meals, meal => {
        return meal.active;
      });

      if (this.filteredView) {
        meals = _.filter(meals, meal => {
          let skip = false;

          if (!skip && filters.tags.length > 0) {
            let hasAllTags = _.reduce(
              filters.tags,
              (has, tag) => {
                if (!has) return false;
                let x = _.includes(meal.tag_titles, tag);

                return x;
              },
              true
            );

            skip = !hasAllTags;
          }

          if (!skip && filters.allergies.length > 0) {
            let hasAllergy = _.reduce(
              meal.allergy_ids,
              (has, allergyId) => {
                if (has) return true;
                let x = _.includes(filters.allergies, allergyId);

                return x;
              },
              false
            );

            skip = hasAllergy;
          }
          return !skip;
        });
      }

      meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (!_.has(grouped, category.category)) {
            grouped[category.category] = [meal];
          } else {
            grouped[category.category].push(meal);
          }
        });
      });

      // Find store-defined category sorting
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      // Structure
      grouped = _.map(grouped, (meals, cat) => {
        return {
          category: cat,
          meals,
          order: sorting[cat] || 9999
        };
      });

      // Sort
      return _.orderBy(grouped, "order");
    },
    categories() {
      let sorting = {};
      this._categories.forEach(cat => {
        sorting[cat.category] = cat.order.toString() + cat.category;
      });

      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.category_ids.forEach(categoryId => {
          let category = _.find(this._categories, { id: categoryId });
          if (!_.includes(grouped, category.category)) {
            grouped.push(category.category);
          }
        });
      });

      return _.orderBy(grouped, cat => {
        return cat in sorting ? sorting[cat] : 9999;
      });
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
    /*
    allergies() {
      let grouped = [];
      this.store.meals.forEach(meal => {
        meal.allergy_ids.forEach(allergyId => {
          let allergy = this.allergies[allergyId];
          if (!_.includes(grouped, allergy.title)) {
            grouped.push(allergy.title);
          }
        });
      });
      return grouped;
    }*/
  },
  beforeDestroy() {
    this.showActiveFilters();
  },
  methods: {
    showActiveFilters() {
      let tags = this.tags;
      this.active = tags.reduce((acc, tag) => {
        acc[tag] = false;
        return acc;
      }, {});

      let allergies = this.allergies;
      this.active = _.reduce(
        allergies,
        (acc, allergy) => {
          acc[allergy] = false;
          return acc;
        },
        {}
      );
    },
    quantity(meal) {
      const qty = this.$store.getters.bagItemQuantity(meal);
      return qty;
    },
    addOne(meal) {
      this.$store.commit("addToBag", { meal, quantity: 1 });
      this.mealModal = false;
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
      this.active[tag] = !this.active[tag];
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.tags, _tag => {
        return tag === _tag;
      });

      i === -1 ? this.filters.tags.push(tag) : Vue.delete(this.filters.tags, i);
    },
    filterByAllergy(allergyId) {
      Vue.set(this.active, allergyId, !this.active[allergyId]);
      this.filteredView = true;

      // Check if filter already exists
      const i = _.findIndex(this.filters.allergies, _allergyId => {
        return _allergyId === allergyId;
      });

      if(i === -1) {
        let allergies = [...this.filters.allergies];
        allergies.push(allergyId);
        Vue.set(this.filters, 'allergies', allergies);
      }
      else {
        Vue.delete(this.filters.allergies, i);
      }
    },
    goToCategory(category) {
      window.location.href = "#" + category;
    },
    viewFilters() {
      this.viewFilterModal = true;
    },
    clearFilters() {
      let allergies = this.filters.allergies;
      _.remove(allergies, allergy => _.includes(allergies, allergy));

      let tags = this.filters.tags;
      _.remove(tags, tag => _.includes(tags, tag));

      this.active = _.mapValues(this.active, () => false);
      this.filteredView = false;
    }
  }
};
</script>
