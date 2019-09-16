<template>
  <div>
    <auth-modal :showAuthModal="showAuthModal"></auth-modal>

    <meal-components-modal ref="componentModal"></meal-components-modal>

    <meal-package-components-modal
      ref="packageComponentModal"
    ></meal-package-components-modal>

    <category-slider></category-slider>

    <div class="menu">
      <store-description-modal
        :showDescriptionModal="showDescriptionModal"
      ></store-description-modal>

      <meal-filter-modal
        :viewFilterModal="viewFilterModal"
        :allergies="allergies"
        :tags="tags"
        @filterByTag="filterByTag($event)"
        @clearFilters="clearFilters"
      ></meal-filter-modal>

      <meal-modal
        :showMealModal="mealModal"
        :meal="meal"
        :slickOptions="slickOptions"
        :storeSettings="storeSettings"
        :mealDescription="mealDescription"
        :ingredients="ingredients"
        :nutritionalFacts="nutritionalFacts"
      ></meal-modal>

      <meal-package-modal
        :mealPackageModal="mealPackageModal"
        :mealPackage="mealPackage"
        :loaded="loaded"
      ></meal-package-modal>

      <div class="row">
        <div :class="`col-md-12 main-menu-area ${storeCSS}`">
          <Spinner
            v-if="!meals.length && !mealPackages.length"
            position="absolute"
          />
          <meals-area
            :meals="meals"
            :card="card"
            :cardBody="cardBody"
            @onCategoryVisible="onCategoryVisible($event)"
            @showMealModal="showMealModal"
          ></meals-area>

          <floating-action-button
            class="d-md-none"
            :style="brandColor"
            to="/customer/bag"
            v-if="!subscriptionId || !adjustOrder"
          >
            <div class="d-flex flex-column h-100">
              <i class="fa fa-shopping-bag text-white"></i>
              <i v-if="total" class="text-white mt-1">{{ total }}</i>
            </div>
          </floating-action-button>

          <meal-packages-area :mealPackages="mealPackages"></meal-packages-area>
        </div>
        <div :class="showBagClass">
          <bag-area
            :manualOrder="manualOrder"
            :adjustOrder="adjustOrder"
            :adjustMealPlan="adjustMealPlan"
            :subscriptionId="subscriptionId"
            :orderId="orderId"
          >
          </bag-area>
          <div class="bag-bottom-area">
            <bag-actions
              :storeView="storeView"
              :manualOrder="manualOrder"
              :adjustOrder="adjustOrder"
              :adjustMealPlan="adjustMealPlan"
              :subscriptionId="subscriptionId"
              :preview="preview"
              :orderId="orderId"
            ></bag-actions>
          </div>
        </div>
        <div :class="showFilterClass">
          <menu-filters> ></menu-filters>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import nutritionFacts from "nutrition-label-jquery-plugin";
import Spinner from "../../components/Spinner";
import MealComponentsModal from "../../components/Modals/MealComponentsModal";
import MealPackageComponentsModal from "../../components/Modals/MealPackageComponentsModal";
import MenuBag from "../../mixins/menuBag";
import units from "../../data/units";
import nutrition from "../../data/nutrition";
import format from "../../lib/format";
import SalesTax from "sales-tax";
import keyboardJS from "keyboardjs";
import LightBox from "vue-image-lightbox";
import "vue-image-lightbox/src/components/style.css";
import { Carousel, Slide } from "vue-carousel";

import CategorySlider from "../../components/Customer/Mobile/CategorySlider";
import OutsideDeliveryArea from "../../components/Customer/OutsideDeliveryArea";
import StoreClosed from "../../components/Customer/StoreClosed";
import StoreDescriptionModal from "../../components/Customer/StoreDescriptionModal";
import MealFilterModal from "../../components/Customer/MealFilterModal";
import MealModal from "../../components/Customer/MealModal";
import MealPackageModal from "../../components/Customer/MealPackageModal";
import MealsArea from "../../components/Customer/MealsArea";
import MealPackagesArea from "../../components/Customer/MealPackagesArea";
import BagArea from "../../components/Customer/BagArea";
import BagActions from "../../components/Customer/BagActions";
import AuthModal from "../../components/Customer/AuthModal";
import MenuFilters from "../../components/Customer/MenuFilters";

window.addEventListener("hashchange", function() {
  window.scrollTo(window.scrollX, window.scrollY - 500);
});

export default {
  components: {
    Spinner,
    SalesTax,
    LightBox,
    Carousel,
    Slide,
    MealComponentsModal,
    MealPackageComponentsModal,
    CategorySlider,
    OutsideDeliveryArea,
    StoreClosed,
    StoreDescriptionModal,
    MealFilterModal,
    MealModal,
    MealPackageModal,
    MealsArea,
    MealPackagesArea,
    BagArea,
    BagActions,
    AuthModal,
    MenuFilters
  },
  mixins: [MenuBag],
  props: {
    storeView: false,
    preview: false,
    manualOrder: false,
    adjustOrder: false,
    adjustMealPlan: false,
    order: {},
    subscription: {},
    subscriptionId: null,
    orderId: null
  },
  data() {
    return {
      showBagClass: "shopping-cart hidden-right bag-area",
      showFilterClass: "shopping-cart hidden-left bag-area",
      search: "",
      showAuthModal: false,
      slickOptions: {
        slidesToShow: 4,
        infinite: false,
        arrows: true,
        prevArrow:
          '<a class="slick-prev"><i class="fa fa-chevron-left"></i></a>',
        nextArrow:
          '<a class="slick-next"><i class="fa fa-chevron-right"></i></a>'
      },
      loaded: false,
      active: {},
      loading: false,
      viewFilterModal: false,
      showDescriptionModal: false,
      filteredView: false,
      filters: {
        tags: [],
        allergies: [],
        categories: []
      },
      meal: null,
      mealPackage: null,
      ingredients: "",
      mealDescription: "",
      mealModal: false,
      mealPackageModal: false,
      nutritionalFacts: {},
      storeCSS: ""
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
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    meals() {
      let meals = this.store.meals;
      let filters = this.filters;
      let grouped = {};

      if (!_.isArray(meals)) {
        return [];
      }

      const search = this.search.toLowerCase();

      // Meal filtering logic
      meals = _.filter(meals, meal => {
        if (
          !meal.active ||
          (this.search && !meal.title.toLowerCase().includes(search))
        ) {
          return false;
        }
        return true;
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
          if (!category) {
            return;
          } else if (!_.has(grouped, category.category)) {
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
    mealPackages() {
      return _.map(
        _.filter(this.store.packages, mealPackage => {
          return mealPackage.active;
        }) || [],
        mealPackage => {
          mealPackage.meal_package = true;
          return mealPackage;
        }
      );
    },
    card() {
      if (this.mobile) {
        return "card border-light mb-0 mt-0 mr-1";
      } else return "";
    },
    cardBody() {
      if (this.mobile) {
        return "card-body border-light mb-0 mt-0 mr-1";
      } else return "";
    },
    storeWebsite() {
      if (!this.storeSettings.website) {
        return null;
      } else {
        let website = this.storeSettings.website;
        if (!website.includes("http")) {
          website = "http://" + website;
        }
        return website;
      }
    },
    mobile() {
      if (window.innerWidth < 500) return true;
      else return false;
    },
    nutrition() {
      return nutrition;
    },
    storeSettings() {
      return this.store.settings;
    },
    remainingMeals() {
      return this.minMeals - this.total;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    },
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "meals";
      }
      return "meal";
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
    showIngredients() {
      return this.storeSettings.showIngredients;
    },
    showBagScrollbar() {
      if (this.bag.length > 7) return true;
      else return false;
    },
    brandColor() {
      let style = "background-color:";
      style += this.store.settings.color;
      return style;
    }
  },
  created() {
    this.$eventBus.$on("showAuthModal", () => {
      this.showAuthModal = true;
    });
    this.$eventBus.$on("showRightBagArea", () => {
      this.showBag();
    });
    this.$eventBus.$on("showFilterArea", () => {
      this.showFilterArea();
    });
  },
  mounted() {
    if (this.storeView) {
      this.storeCSS = "store-menu-view";
      this.showBagClass = "shopping-cart show-right bag-area area-scroll";
    }
    keyboardJS.bind("left", () => {
      if (this.$refs.carousel) {
        console.log(this.$refs.carousel);
        this.$refs.carousel.handleNavigation("backward");
      }
    });
    keyboardJS.bind("right", () => {
      if (this.$refs.carousel) {
        this.$refs.carousel.handleNavigation("forward");
      }
    });
  },
  beforeDestroy() {
    this.showActiveFilters();
  },
  methods: {
    ...mapActions([
      "refreshSubscriptions",
      "emptyBag",
      "refreshUpcomingOrders"
    ]),
    ...mapMutations(["emptyBag", "setBagMealPlan", "setBagCoupon"]),
    onCategoryVisible(isVisible, index) {
      if (isVisible && this.$refs.categorySlider) {
        this.$refs.categorySlider.goTo(index);
      }
    },
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
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    showMealModal(meal) {
      this.meal = meal;
      this.mealModal = true;
      this.mealDescription = meal.description
        ? meal.description.replace(/\n/g, "<br>")
        : "";

      this.$nextTick(() => {
        this.getNutritionFacts(this.meal.ingredients, this.meal);
        // this.$refs.mealGallery.reSlick();
      });
    },
    showMealPackageModal(mealPackage) {
      this.mealPackage = { ...mealPackage };
      this.mealPackageModal = true;

      this.$nextTick(() => {
        this.mealPackage.meals.forEach(meal => {
          this.getNutritionFacts(
            meal.ingredients,
            meal,
            this.$refs[`nutritionFacts${meal.id}`]
          );
        });
      });
    },
    filterByTag(tag) {
      Vue.set(this.active, tag, !this.active[tag]);
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

      if (i === -1) {
        let allergies = [...this.filters.allergies];
        allergies.push(allergyId);
        Vue.set(this.filters, "allergies", allergies);
      } else {
        Vue.delete(this.filters.allergies, i);
      }
    },
    clearFilters() {
      let allergies = this.filters.allergies;
      _.remove(allergies, allergy => _.includes(allergies, allergy));

      let tags = this.filters.tags;
      _.remove(tags, tag => _.includes(tags, tag));

      this.active = _.mapValues(this.active, () => false);
      this.filteredView = false;
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
    addMealOrdersToBag() {
      //conact item with meal
      this.order.items.forEach(item => {
        const meal = this.getMeal(item.meal_id);

        if (!meal) {
          return;
        }

        let components = _.mapValues(
          _.groupBy(item.components, "meal_component_id"),
          choices => {
            return _.map(choices, "meal_component_option_id");
          }
        );

        let addons = _.map(item.addons, "meal_addon_id");

        for (let i = 0; i < item.quantity; i++) {
          this.addOne(meal, false, item.meal_size_id, components, addons);
        }
      });
    },
    addMealsSubscriptionToBag() {
      //conact item with meal
      this.subscription.items.forEach(item => {
        const meal = this.getMeal(item.meal_id);

        if (!meal) {
          return;
        }

        let components = _.mapValues(
          _.groupBy(item.components, "meal_component_id"),
          choices => {
            return _.map(choices, "meal_component_option_id");
          }
        );

        let addons = _.map(item.addons, "meal_addon_id");

        for (let i = 0; i < item.quantity; i++) {
          this.addOne(meal, false, item.meal_size_id, components, addons);
        }
      });
    },
    getNutritionFacts(ingredients, meal, ref = null) {
      const nutrition = this.nutrition.getTotals(ingredients);
      const ingredientList = this.nutrition.getIngredientList(ingredients);
      this.nutritionalFacts = {
        showServingUnitQuantity: false,
        itemName: meal.title,
        ingredientList: ingredientList,
        showIngredients: this.showIngredients,
        decimalPlacesForQuantityTextbox: 2,
        valueServingUnitQuantity: 1,
        allowFDARounding: true,
        decimalPlacesForNutrition: 2,
        showPolyFat: false,
        showMonoFat: false,
        valueCalories: nutrition.calories,
        valueFatCalories: nutrition.fatCalories,
        valueTotalFat: nutrition.totalFat,
        valueSatFat: nutrition.satFat,
        valueTransFat: nutrition.transFat,
        valueCholesterol: nutrition.cholesterol,
        valueSodium: nutrition.sodium,
        valueTotalCarb: nutrition.totalCarb,
        valueFibers: nutrition.fibers,
        valueSugars: nutrition.sugars,
        valueProteins: nutrition.proteins,
        valueVitaminD: (nutrition.vitaminD / 20000) * 100,
        valuePotassium_2018: (nutrition.potassium / 4700) * 100,
        valueCalcium: (nutrition.calcium / 1300) * 100,
        valueIron: (nutrition.iron / 18) * 100,
        valueAddedSugars: nutrition.addedSugars,
        showLegacyVersion: false
      };
    },
    showBag() {
      if (this.showBagClass.includes("hidden-right")) {
        this.showBagClass = "shopping-cart show-right bag-area";
        if (this.showBagScrollbar) {
          this.showBagClass += " area-scroll";
        }
      } else if (this.showBagClass.includes("show-right")) {
        this.showBagClass = "shopping-cart hidden-right bag-area";
        if (this.showBagScrollbar) {
          this.showBagClass += " area-scroll";
        }
      }
    },
    showFilterArea() {
      if (this.showFilterClass === "shopping-cart hidden-left bag-area")
        this.showFilterClass = "shopping-cart show-left bag-area";
      else if (this.showFilterClass === "shopping-cart show-left bag-area")
        this.showFilterClass = "shopping-cart hidden-left bag-area";
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
    }
  }
};
</script>
