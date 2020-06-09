<template>
  <div :class="mealPageClass" v-if="showPage" style="min-height: 100%;">
    <div class="row">
      <div class="col-md-6">
        <div>
          <button
            type="button"
            class="btn btn-lg btn-secondary d-inline mb-2 width-100 mr-2"
            @click="back"
          >
            <h6 class="strong pt-1 dark-gray">Back</h6>
          </button>
          <button
            type="button"
            :style="brandColor"
            class="btn btn-lg white-text d-inline mb-2"
            @click="addMeal(meal)"
            v-if="smallScreen"
          >
            <h6 class="strong pt-1">Add To Bag</h6>
          </button>
        </div>
      </div>
    </div>
    <div class="row">
      <div :class="imageClass">
        <div class="d-flex">
          <div
            v-if="!smallScreen && showNutritionFacts"
            style="width:80px"
            class="pr-2 d-inline"
          >
            <img
              :src="meal.image.url_thumb"
              style="width:60px;height:60px"
              v-if="showNutritionFacts"
              @mouseover="showcaseNutrition = false"
            />
            <img
              src="/images/nutrition-thumb.jpg"
              v-if="showNutritionFacts"
              @mouseover="showcaseNutrition = true"
              class="pt-2"
            />
          </div>
          <thumbnail
            v-if="meal.image != null && meal.image.url && !showcaseNutrition"
            :src="meal.image.url"
            :aspect="false"
            width="100%"
            @click="$emit('show-gallery', getMealGallery(meal), 0)"
            class="mealPageImage d-inline"
          ></thumbnail>
          <div style="width:323px" v-if="showcaseNutrition">
            <div
              id="nutritionFacts"
              ref="nutritionFacts"
              class="pt-2 d-inline"
            ></div>
          </div>
        </div>
        <slick
          ref="mealGallery"
          :options="slickOptions"
          class="pt-2"
          :style="galleryStyle"
        >
          <div v-for="(image, i) in getMealGallery(meal)" :key="image.id">
            <div style="image">
              <thumbnail
                v-if="image.url"
                :src="image.url"
                :aspect="true"
                :lazy="false"
                :spinner="false"
                :width="'70px'"
                @click="$emit('show-gallery', getMealGallery(meal), i)"
              ></thumbnail>
            </div>
          </div>
        </slick>
      </div>
      <div v-if="smallScreen && showNutritionFacts">
        <div id="nutritionFacts" ref="nutritionFacts" class="pt-2"></div>
      </div>

      <div class="col-md-6">
        <h2 class="dark-gray">{{ meal.title }}</h2>
        <h4 class="mt-2 dark-gray">
          {{ format.money(mealVariationPrice, storeSettings.currency) }}
        </h4>
        <div class="mt-3 d-flex" style="flex-wrap:wrap">
          <span
            class="badge badge-success d-inline mr-1 mt-1 tags"
            v-for="(tag, index) in meal.tags"
            v-bind:key="index"
            >{{ tag.tag }}</span
          >
        </div>
        <div class="mt-2 d-flex" style="flex-wrap:wrap">
          <span
            class="badge badge-warning d-inline mr-1 mt-1 tags dark-gray"
            v-for="(allergy, index) in meal.allergy_titles"
            v-bind:key="index"
            >{{ allergy }}</span
          >
        </div>
        <div
          v-if="
            meal.macros && storeSettings.showMacros && meal.macros.macros_filled
          "
          class="macros mt-2 meal-page-text"
        >
          <li>
            <span
              v-if="storeSettings.macrosFromNutrition && !blankNutritionFacts"
            >
              Calories:
              <p class="d-inline">{{ nutritionalFacts.valueCalories }}</p>
            </span>
            <span v-else>
              <span
                v-if="
                  !isNaN(meal.macros.calories) && meal.macros.calories !== null
                "
                >Calories:
                <p class="d-inline">{{ meal.macros.calories }}</p></span
              >
            </span>
          </li>
          <li>
            <span
              v-if="storeSettings.macrosFromNutrition && !blankNutritionFacts"
            >
              Carbs:
              <p class="d-inline">{{ nutritionalFacts.valueTotalCarb }}</p>
            </span>
            <span v-else>
              <span
                v-if="
                  !isNaN(meal.macros.calories) && meal.macros.carbs !== null
                "
                >Carbs:
                <p class="d-inline">{{ meal.macros.carbs }}</p></span
              >
            </span>
          </li>
          <li>
            <span
              v-if="storeSettings.macrosFromNutrition && !blankNutritionFacts"
            >
              Protein:
              <p class="d-inline">{{ nutritionalFacts.valueProteins }}</p>
            </span>
            <span v-else>
              <span
                v-if="
                  !isNaN(meal.macros.protein) && meal.macros.protein !== null
                "
                >Protein:
                <p class="d-inline">{{ meal.macros.protein }}</p></span
              >
            </span>
          </li>
          <li>
            <span
              v-if="storeSettings.macrosFromNutrition && !blankNutritionFacts"
            >
              Fat:
              <p class="d-inline">{{ nutritionalFacts.valueTotalFat }}</p>
            </span>
            <span v-else>
              <span v-if="!isNaN(meal.macros.fat) && meal.macros.fat !== null"
                >Fat:
                <p class="d-inline">{{ meal.macros.fat }}</p></span
              >
            </span>
          </li>
        </div>
        <div class="meal-page-text">
          <p v-html="mealDescription" class="mt-3"></p>
        </div>
        <div class="meal-page-text">
          <p
            v-if="
              storeSettings.mealInstructions &&
                meal.instructions != null &&
                meal.instructions != ''
            "
          >
            Instructions: {{ meal.instructions }}
          </p>
        </div>
        <div class="meal-page-text">
          <p v-if="store.settings.showIngredients && mealIngredients != ''">
            Ingredients: {{ mealIngredients }}
          </p>
        </div>
        <div class="meal-page-text">
          <p v-if="meal.expirationDays">
            Consume within {{ meal.expirationDays }} days.
          </p>
        </div>
        <div v-if="!smallScreen && showNutritionFacts">
          <p
            class="font-12 strong"
            :style="brandColorText"
            @click="showcaseNutrition = !showcaseNutrition"
            v-if="!showcaseNutrition"
          >
            Show Nutrition
          </p>
          <p
            class="font-12 strong"
            :style="brandColorText"
            @click="showcaseNutrition = !showcaseNutrition"
            v-if="showcaseNutrition"
          >
            Show Meal
          </p>
        </div>
        <div>
          <b-form-textarea
            v-if="
              (storeModules.specialInstructions &&
                !storeModuleSettings.specialInstructionsStoreOnly) ||
                (storeModuleSettings.specialInstructionsStoreOnly &&
                  ($route.params.storeView || $route.params.orderId))
            "
            class="mt-4 mb-2"
            v-model="special_instructions"
            placeholder="Special instructions"
            rows="3"
            max-rows="6"
          ></b-form-textarea>
        </div>

        <div class="row">
          <div
            class="col-md-5"
            v-if="
              !smallScreen &&
                storeSettings.showNutrition &&
                !blankNutritionFacts
            "
          ></div>
          <div class="col-md-12">
            <div>
              <b-form-radio-group
                buttons
                v-model="mealSize"
                :options="sizes"
                class="filters small flexibleButtonGroup mb-2"
                required
                @input="changeSize"
                v-show="sizes && sizes.length > 1"
              ></b-form-radio-group>

              <meal-variations-area
                id="meal-variations-area"
                :meal="meal"
                :sizeId="mealSize"
                :invalid="invalid"
                ref="componentModal"
                :key="total"
              ></meal-variations-area>
            </div>
            <div class="d-flex">
              <button
                type="button"
                class="btn btn-lg btn-secondary d-inline mb-2 width-100 mr-2"
                @click="back"
                v-if="smallScreen"
              >
                <h6 class="strong pt-1 dark-gray">Back</h6>
              </button>
              <button
                type="button"
                :style="brandColor"
                class="btn btn-lg white-text d-inline mb-2"
                @click="addMeal(meal)"
              >
                <h6 class="strong pt-1">Add To Bag</h6>
              </button>
              <!-- <button
                type="button"
                class="btn btn-md btn-secondary d-inline"
                @click="back"
              >
                <h6 class="strong pt-1 dark-gray">Back</h6>
              </button> -->
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
      selectedAddons: [],
      showcaseNutrition: false
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
    manualOrder: false,
    sizeSelection: null
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
    galleryStyle() {
      if (this.showNutritionFacts) {
        return "margin-left:70px;";
      } else return "";
    },
    showNutritionFacts() {
      if (this.storeSettings.showNutrition && !this.blankNutritionFacts) {
        return true;
      } else return false;
    },
    mealIngredients() {
      let ingredients = "";
      this.meal.ingredients.forEach(ingredient => {
        ingredients += ingredient.food_name + ", ";
      });
      ingredients = ingredients.substring(0, ingredients.length - 2);
      return ingredients;
    },
    imageClass() {
      if (this.meal.media.length > 0) {
        return "col-md-6";
      } else {
        return "hide";
      }
    },
    smallScreen() {
      const width =
        window.innerWidth ||
        document.documentElement.clientWidth ||
        document.body.clientWidth;
      if (width < 1150) {
        return true;
      } else {
        return false;
      }
    },
    brandColor() {
      if (this.store.settings) {
        let style = "background-color:";
        style += this.store.settings.color;
        return style;
      }
    },
    brandColorText() {
      if (this.store.settings) {
        let style = "color:";
        style += this.store.settings.color;
        return style;
      }
    },
    blankNutritionFacts() {
      let nutrition = this.nutritionalFacts;
      if (
        nutrition.valueCalories === "0" &&
        nutrition.valueTotalCarb === "0" &&
        nutrition.valueTotalFat === "0" &&
        nutrition.valueProteins === "0"
      ) {
        return true;
      } else {
        return false;
      }
    },
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
      //$(ref).nutritionLabel(null);
      //let ref = this.$refs.nutritionFacts;
      //$(ref).nutritionLabel(this.nutritionalFacts);

      $("#nutritionFacts").nutritionLabel(null);
      $("#nutritionFacts").nutritionLabel(this.nutritionalFacts);
      $("#nutritionFacts1").nutritionLabel(null);
      $("#nutritionFacts1").nutritionLabel(this.nutritionalFacts);
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
        this.scrollToValidations();
        this.$toastr.w("Please select the minimum/maximum required choices.");
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
      this.showcaseNutrition = false;
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
      this.showcaseNutrition = false;
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
          if (this.components && JSON.stringify(this.components != "{}")) {
            for (let i in this.components) {
              const component = this.components[i];

              if (
                component.includes(option.id) &&
                option.ingredients &&
                option.ingredients.length > 0
              ) {
                if (
                  !this.selectedComponentOptions.includes(option.ingredients[0])
                ) {
                  this.selectedComponentOptions.push(option.ingredients[0]);
                } else {
                  this.selectedComponentOptions = this.selectedComponentOptions.filter(
                    (value, index, arr) => {
                      return value != option.ingredients[0];
                    }
                  );
                }
              }
            }
          }
          /*
          if (this.components[1] != null)
            if (this.components[1].includes(option.id))
              if (
                !this.selectedComponentOptions.includes(option.ingredients[0])
              )
                this.selectedComponentOptions.push(option.ingredients[0]);
              else this.selectedComponentOptions.pop(option.ingredients[0]);
          */
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
    },
    setSizeFromMealsArea(size) {
      this.sizeChanged = true;
      this.mealSize = size;
    },
    scrollToValidations() {
      let element = document.getElementById("meal-variations-area");
      element.scrollIntoView();
      window.scrollBy(0, -130);
    }
  }
};
</script>
