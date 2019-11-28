<template>
  <div :class="mealPageClass" v-if="showPage" style="min-height: 100%;">
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
                ($route.params.storeView || $route.params.orderId))
          "
          class="mt-4"
          v-model="special_instructions"
          placeholder="Special instructions"
          rows="3"
          max-rows="6"
        ></b-form-textarea>

        <div class="row mt-4" v-if="storeSettings.menuStyle === 'image'">
          <div class="col-md-2">
            <h2 class="pt-3">
              {{ format.money(mealVariationPrice, storeSettings.currency) }}
            </h2>
          </div>
          <div class="col-md-3 offset-1">
            <b-btn @click="addMeal(meal)" class="menu-bag-btn">ADD</b-btn>
          </div>
        </div>
        <div class="row mt-4" v-if="storeSettings.menuStyle === 'text'">
          <div class="col-md-2">
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
      mealSizes: null,
      mealSize: null,
      components: null,
      addons: [],
      sizeChanged: false,
      invalidCheck: false,
      invalid: false,
      special_instructions: null,
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
    nutritionalFacts: {},
    adjustOrder: false,
    manualOrder: false
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
    isMultipleDelivery() {
      return this.store.modules.multipleDeliveryDays == 1 ? true : false;
    },
    showPage() {
      if (this.meal) {
        let mealSizes = null;
        let meal = this.meal;
        let sizes = meal.sizes;

        if (sizes) {
          mealSizes = [];

          mealSizes.push({
            full_title:
              meal.title + " - " + meal.default_size_title || "Regular",
            id: null,
            price: meal.item_price,
            title: meal.default_size_title || "Regular",
            defaultAdded: true
          });

          for (let i in sizes) {
            mealSizes.push(sizes[i]);
          }
        }

        this.mealSizes = mealSizes;
      }

      return this.$parent.mealPageView;
    },
    viewedMeal() {
      return this.meal;
    },
    sizes() {
      if (this.mealSizes) {
        return Object.values(this.mealSizes).map(size => {
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
        (this.mealSizes && this.mealSizes.length > 1) ||
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
    isManualOrder() {
      if (
        this.manualOrder ||
        this.$route.params.manualOrder ||
        this.$route.name == "store-manual-order"
      ) {
        return true;
      }
      return false;
    },
    isAdjustOrder() {
      if (
        this.adjustOrder ||
        this.$route.params.adjustOrder ||
        this.$route.name == "store-adjust-order"
      ) {
        return true;
      }
      return false;
    },
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
    getPackageBagItems() {
      const items = [];
      const bag = this.bag;

      if (bag) {
        bag.forEach(item => {
          if (item.meal_package) {
            if (!this.isMultipleDelivery) {
              items.push(item);
            } else {
              if (
                item.delivery_day &&
                this.store.delivery_day &&
                item.delivery_day.id == this.store.delivery_day.id
              ) {
                items.push(item);
              }
            }
          }
        });
      }

      return items;
    },
    addMeal(meal) {
      if (this.invalidCheck && this.hasVariations) {
        this.invalid = true;
        return;
      }

      let size = this.mealSize;

      if (this.isAdjustOrder() || this.isManualOrder()) {
        const items = this.getPackageBagItems();
        if (items && items.length > 0) {
          this.$parent.showAdjustModal(
            meal,
            size,
            this.components,
            this.addons,
            this.special_instructions,
            items
          );
          return;
        } else {
          this.addOne(
            meal,
            false,
            size,
            this.components,
            this.addons,
            this.special_instructions
          );
        }
      } else {
        this.addOne(
          meal,
          false,
          size,
          this.components,
          this.addons,
          this.special_instructions
        );
      }

      this.mealSize = null;
      this.back();
      if (this.$parent.showBagClass.includes("hidden")) this.$parent.showBag();
      this.mealSize = null;
      this.components = null;
      this.addons = [];
      this.defaultMealSize = null;
      this.special_instructions = null;
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
      this.mealSize = null;
      this.components = null;
      this.addons = [];
      this.defaultMealSize = null;
      this.special_instructions = null;
      this.invalid = false;
      this.sizeChanged = false;
      this.addons = [];
      this.$parent.showMealsArea = true;
      this.$parent.showMealPackagesArea = true;
      this.$parent.mealPageView = false;
      this.mealSizePrice = null;
      this.invalidCheck = false;
    },
    getMealVariationPrice() {
      let selectedMealSize = null;

      if (this.mealSizes) {
        selectedMealSize = _.find(this.mealSizes, size => {
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
      let size = _.filter(this.mealSizes, size => {
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
        if (
          this.addons != null &&
          this.addons.includes(addon.id) &&
          addon.ingredients &&
          addon.ingredients.length > 0
        ) {
          if (!this.selectedAddons.includes(addon.ingredients[0])) {
            this.selectedAddons.push(addon.ingredients[0]);
          }
        }
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
