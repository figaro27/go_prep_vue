<template>
  <div
    :class="mealPageClass"
    v-if="$parent.mealPageView"
    style="min-height: 100%;"
  >
    <div class="meal-page mt-5 mb-3 flexibleRow">
      <div class="flexibleArea">
        <div class="row">
          <div class="col-md-4">
            <h5>{{ meal.title }}</h5>
            <thumbnail
              v-if="meal.image != null && meal.image.url"
              :src="meal.image.url"
              :aspect="false"
              width="100%"
              @click="$refs.lightbox.showImage(0)"
            ></thumbnail>
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
          </div>
          <div class="col-md-3" v-if="meal.tags && meal.tags.length > 0">
            <h5>Nutrition</h5>
            <li v-bind:key="index" v-for="(tag, index) in meal.tags">
              {{ tag.tag }}
            </li>
          </div>

          <p v-html="mealDescription" class="mt-3"></p>

          <div
            class="col-md-3"
            v-if="meal.allergy_titles && meal.allergy_titles.length > 0"
          >
            <h5>Allergies</h5>
            <li
              v-bind:key="index"
              v-for="(allergy, index) in meal.allergy_titles"
            >
              {{ allergy }}
            </li>
          </div>
          <div class="col-md-2">
            <b-btn @click="back">BACK</b-btn>
          </div>
        </div>
        <p v-html="mealDescription" class="mt-3"></p>

        <b-form-radio-group
          buttons
          v-model="mealSize"
          :options="sizes"
          class="filters small flexibleButtonGroup"
          required
          @input="changeSize"
          v-show="sizes && sizes.length > 1"
        ></b-form-radio-group>

        <meal-variations-area
          :meal="meal"
          :sizeId="mealSize"
          :invalid="invalid"
          ref="componentModal"
          :key="total"
        ></meal-variations-area>
        <div class="title mt-3" v-if="meal.macros && storeSettings.showMacros">
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

        <b-form-textarea
          v-if="
            (storeModules.specialInstructions &&
              !storeModuleSettings.specialInstructionsStoreOnly) ||
              (storeModuleSettings.specialInstructionsStoreOnly &&
                $route.params.storeView)
          "
          class="mt-4"
          v-model="specialInstructions"
          placeholder="Special instructions"
          rows="3"
          max-rows="6"
        ></b-form-textarea>

        <div class="row mt-4" v-if="storeSettings.menuStyle === 'image'">
          <div class="col-md-1">
            <h2 class="pt-3">
              {{ format.money(mealVariationPrice, storeSettings.currency) }}
            </h2>
          </div>
          <div class="col-md-3 offset-1">
            <b-btn @click="addMeal(meal)" class="menu-bag-btn">ADD</b-btn>
          </div>
        </div>
        <div class="row mt-4" v-if="storeSettings.menuStyle === 'text'">
          <div class="col-md-1">
            <h2 class="pt-3">
              {{ format.money(mealVariationPrice, storeSettings.currency) }}
            </h2>
          </div>
          <div class="col-md-3 offset-1">
            <b-btn @click="addMeal(meal)" class="menu-bag-btn">ADD</b-btn>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 mt-3">
            <h4
              v-if="
                storeSettings.mealInstructions &&
                  meal.instructions != '' &&
                  meal.instructions != null
              "
            >
              Instructions
            </h4>
            <p v-if="storeSettings.mealInstructions && meal.instructions != ''">
              {{ meal.instructions }}
            </p>
          </div>
        </div>
        <div class="row mb-3 mt-3" v-if="storeSettings.showNutrition">
          <div class="col-xl-3 col-lg-6">
            <div id="nutritionFacts" ref="nutritionFacts"></div>
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
      components: null,
      addons: [],
      sizeChanged: false,
      invalidCheck: false,
      invalid: false,
      specialInstructions: null,
      mealVariationPrice: null,
      totalAddonPrice: null,
      totalComponentPrice: null,
      selectedComponentOptions: [],
      selectedAddons: []
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
      getMealPackage: "viewedStoreMealPackage",
      storeModules: "viewedStoreModules",
      storeModuleSettings: "viewedStoreModuleSettings"
    }),
    // columns() {
    //   if (this.storeSettings.menuStyle === "image") return "col-md-8";
    //   else return "col-md-12";
    // },
    viewedMeal() {
      return this.meal;
    },
    sizes() {
      let meal = this.meal;
      let sizes = meal.sizes;
      let sizeCheck = false;

      if (sizes) {
        sizes.forEach(size => {
          if (size.defaultAdded) sizeCheck = true;
        });
      }

      if (!sizeCheck && sizes) {
        sizes.unshift({
          full_title: meal.title + " - " + meal.default_size_title || "Regular",
          id: null,
          price: meal.item_price,
          title: meal.default_size_title || "Regular",
          defaultAdded: true
        });
      }

      if (sizes) {
        return Object.values(sizes).map(size => {
          return {
            text: size.title,
            price: size.price,
            value: size.id
          };
        });
      } else {
        return [];
      }
    },
    hasVariations() {
      if (
        (this.meal.sizes && this.meal.sizes.length > 1) ||
        (this.meal.components && this.meal.components.length > 0) ||
        (this.meal.addons && this.meal.addons.length > 0)
      )
        return true;
      else return false;
    },
    mealPageClass() {
      if (this.storeSettings.showNutrition) {
        return "main-customer-container box-shadow";
      } else return "main-customer-container box-shadow full-height";
    }
  },
  updated() {
    this.getNutritionFacts();
    if (!this.sizeChanged && this.sizes && this.sizes.length > 0) {
      this.mealSize = this.sizes[0].value;
    }
    this.getMealVariationPrice();
    this.totalAddonPrice = 0;
    this.totalComponentPrice = 0;
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
      // if (!this.sizeChanged) {
      $(ref).nutritionLabel(null);
      let ref = this.$refs.nutritionFacts;
      $(ref).nutritionLabel(this.nutritionalFacts);
      // }
    },
    addMeal(meal) {
      if (this.invalidCheck && this.hasVariations) {
        this.invalid = true;
        return;
      }

      // if (this.hasVariations) {
      //   this.addOne(meal, false, this.mealSize, this.components, this.addons, this.specialInstructions);
      // } else {
      //   this.addOne(meal);
      // }

      let size = null;
      if (this.sizes && this.sizes.length > 1) {
        size = this.mealSize;
      }

      meal.item_title = meal.full_title;

      this.addOne(
        meal,
        false,
        size,
        this.components,
        this.addons,
        this.specialInstructions
      );

      this.mealSize = null;
      this.back();
      if (this.$parent.showBagClass.includes("hidden")) this.$parent.showBag();
      this.mealSize = null;
      this.components = null;
      this.addons = [];
      this.defaultMealSize = null;
      this.specialInstructions = null;
      this.invalid = false;
    },
    back() {
      let viewedMeal = {};

      if (this.$parent.meals) {
        this.$parent.meals.forEach(category => {
          if (category.meals) {
            category.meals.forEach(menuMeal => {
              if (this.meal.id === menuMeal.id) viewedMeal = menuMeal;
            });
          }
        });
      }

      viewedMeal.meal_page_visited = true;
      this.sizeChanged = false;
      this.addons = [];
      if (this.meal.sizes && this.meal.sizes.length === 1) {
        this.$parent.resetMeal = true;
      }
      this.$parent.showMealsArea = true;
      this.$parent.showMealPackagesArea = true;
      this.$parent.mealPageView = false;
      this.mealSizePrice = null;
      this.invalidCheck = false;
    },
    getMealVariationPrice() {
      let selectedMealSize = null;

      if (this.meal.sizes) {
        selectedMealSize = _.find(this.meal.sizes, size => {
          return size.id === this.mealSize;
        });
      }

      if (selectedMealSize) {
        this.mealVariationPrice =
          selectedMealSize.price +
          this.totalAddonPrice +
          this.totalComponentPrice;
      }
    },
    changeSize(mealSizeId) {
      // this.$refs.componentModal.resetVariations();
      this.components = [];
      this.addons = [];
      this.selectedComponentOptions = [];
      this.selectedAddons = [];
      this.sizeChanged = true;
      this.$refs.componentModal.resetVariations();
      this.addons = [];
      this.components = null;
      this.refreshNutritionFacts();
    },
    getSizeIngredients() {
      let size = _.filter(this.meal.sizes, size => {
        return size.id === this.mealSize;
      });
      return {
        ingredients: size[0].ingredients,
        servingsPerMeal: size[0].servingsPerMeal,
        servingSizeUnit: size[0].servingSizeUnit
      };
    },
    getComponentIngredients() {
      this.meal.components.forEach(component => {
        component.options.forEach(option => {
          if (this.components[1] != null)
            if (this.components[1].includes(option.id))
              if (
                !this.selectedComponentOptions.includes(option.ingredients[0])
              )
                this.selectedComponentOptions.push(option.ingredients[0]);
              else this.selectedComponentOptions.pop(option.ingredients[0]);
        });
      });
      this.refreshNutritionFacts();
    },
    getAddonIngredients() {
      this.meal.addons.forEach(addon => {
        if (this.addons != null)
          if (this.addons.includes(addon.id))
            if (!this.selectedAddons.includes(addon.ingredients[0]))
              this.selectedAddons.push(addon.ingredients[0]);
            else this.selectedAddons.pop(addon.ingredients[0]);
      });
      this.refreshNutritionFacts();
    },
    refreshNutritionFacts() {
      let sizeIngredients = this.meal.ingredients;
      let servingDetails = {
        servingsPerMeal: this.meal.servingsPerMeal,
        servingSizeUnit: this.meal.servingSizeUnit
      };
      if (this.mealSize != null) {
        sizeIngredients = this.getSizeIngredients().ingredients;
        servingDetails = this.getSizeIngredients();
      }
      let componentsIngredients = sizeIngredients.concat(
        this.selectedComponentOptions
      );
      let allIngredients = componentsIngredients.concat(this.selectedAddons);

      this.$parent.getNutritionFacts(
        allIngredients,
        this.meal,
        null,
        servingDetails
      );
      this.$nextTick(() => {
        this.getNutritionFacts();
      });
    }
  }
};
</script>
