<template>
  <div>
    <div
      class="main-customer-container box-shadow"
      style="height:100vh"
      v-if="$parent.mealPageView"
    >
      <div class="row meal-page">
        <div class="col-md-6">
          <thumbnail
            v-if="meal.image.url"
            :src="meal.image.url"
            :aspect="false"
            width="100%"
            @click="$refs.lightbox.showImage(0)"
          ></thumbnail>
          <img v-else :src="meal.featured_image" />

          <LightBox
            ref="lightbox"
            :images="getMealGallery(meal)"
            :showLightBox="false"
          ></LightBox>

          <slick ref="mealGallery" :options="slickOptions">
            <div v-for="(image, i) in getMealGallery(meal)" :key="image.id">
              <div style="image">
                <thumbnail
                  v-if="image.url"
                  :src="image.url"
                  :aspect="true"
                  :lazy="false"
                  :spinner="false"
                  :width="'70px'"
                  @click="$refs.lightbox.showImage(i)"
                ></thumbnail>
              </div>
            </div>
          </slick>
          <div class="row">
            <div
              class="col-md-8 offset 2"
              id="nutritionFacts"
              ref="nutritionFacts"
            ></div>
            <!-- <div class="col-md-3" v-if="storeSettings.showIngredients">
              <h5>Ingredients</h5>
              <li v-for="ingredient in meal.ingredients">
                {{ ingredient.food_name }}
              </li>
            </div> -->
          </div>
        </div>
        <div class="col-md-6">
          <div class="row">
            <div class="col-md-4">
              <h5>Nutrition</h5>
              <li v-for="tag in meal.tags">{{ tag.tag }}</li>
            </div>
            <div class="col-md-4">
              <h5>Allergies</h5>
              <li v-for="allergy in meal.allergy_titles">
                {{ allergy }}
              </li>
            </div>
            <div class="col-md-4">
              <b-btn @click="back">BACK</b-btn>
            </div>
          </div>
          <p v-html="mealDescription" class="mt-3"></p>

          <b-form-radio-group
            buttons
            v-model="mealSize"
            :options="sizes"
            class="filters small"
            required
            @change="sizeChanged = true"
            v-show="sizes.length > 1"
          ></b-form-radio-group>

          <meal-variations-area
            :meal="meal"
            :sizeId="mealSize"
            :invalid="invalid"
            ref="componentModal"
            :key="total"
          ></meal-variations-area>
          <div
            class="title mt-3"
            v-if="meal.macros && storeSettings.showMacros"
          >
            <div class="row">
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Calories</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.calories }}
                  </p>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Carbs</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.carbs }}
                  </p>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Protein</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.protein }}
                  </p>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="row">
                  <p class="small strong col-6 col-md-12">Fat</p>
                  <p class="small col-6 col-md-12">
                    {{ meal.macros.fat }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-md-2">
              <h2 class="pt-3">
                {{ format.money(meal.price, storeSettings.currency) }}
              </h2>
            </div>
            <div class="col-md-9 offset-1">
              <b-btn @click="addMeal(meal)" class="menu-bag-btn">ADD</b-btn>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";
import LightBox from "vue-image-lightbox";
import nutritionFacts from "nutrition-label-jquery-plugin";
import keyboardJS from "keyboardjs";
import units from "../../data/units";
import nutrition from "../../data/nutrition";
import format from "../../lib/format";
import "vue-image-lightbox/src/components/style.css";
import { Carousel, Slide } from "vue-carousel";
import MealVariationsArea from "../../components/Modals/MealVariationsArea";

export default {
  data() {
    return {
      defaultMealSize: {},
      mealSize: -1,
      components: {},
      addons: [],
      sizeChanged: false,
      invalidCheck: false,
      invalid: false
    };
  },
  components: {
    LightBox,
    MealVariationsArea
  },
  props: {
    showMealModal: false,
    meal: {},
    slickOptions: {},
    storeSettings: {},
    mealDescription: "",
    ingredients: "",
    nutritionalFacts: {}
  },
  mixins: [MenuBag],
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
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    viewedMeal() {
      return this.meal;
    },
    sizes() {
      let meal = this.meal;
      let sizes = meal.sizes;
      let sizeCheck = false;
      sizes.forEach(size => {
        if (size.defaultAdded) sizeCheck = true;
      });

      if (!sizeCheck) {
        sizes.unshift({
          full_title: meal.title + " - " + meal.default_size_title || "Regular",
          id: meal.id,
          price: meal.item_price,
          title: meal.default_size_title || "Regular",
          defaultAdded: true
        });
      }

      return Object.values(sizes).map(size => {
        return {
          text: size.title,
          price: size.price,
          value: size.id
        };
      });
    },
    hasVariations() {
      if (
        this.meal.sizes.length > 1 ||
        this.meal.components.length > 0 ||
        this.meal.addons.length > 0
      )
        return true;
      else return false;
    }
  },
  updated() {
    this.getNutritionFacts();
    if (!this.sizeChanged) {
      this.mealSize = this.sizes[0].value;
    }
  },
  methods: {
    getMealGallery(meal) {
      return meal.gallery.map((item, i) => {
        return {
          id: i,
          url: item.url_original,
          src: item.url_original,
          thumb: item.url_thumb
        };
      });
    },
    getNutritionFacts() {
      let ref = this.$refs.nutritionFacts;
      this.$nextTick(() => {
        this.$nextTick(() => {
          $(ref).nutritionLabel(this.nutritionalFacts);
        });
      });
    },
    addMeal(meal) {
      if (this.invalidCheck && this.hasVariations) {
        this.invalid = true;
        return;
      }

      if (this.hasVariations) {
        this.addOne(meal, false, this.mealSize, this.components, this.addons);
      } else {
        this.addOne(meal);
      }

      this.mealSize = null;
      this.back();
      if (this.$parent.showBagClass.includes("hidden")) this.$parent.showBag();
      this.mealSize = null;
      this.components = null;
      this.addons = [];
      this.defaultMealSize = null;
      this.invalid = false;
    },
    back() {
      this.sizeChanged = false;
      this.addons = [];
      this.$parent.showMealsArea = true;
      this.$parent.showMealPackagesArea = true;
      this.$parent.mealPageView = false;
    }
  }
};
</script>
