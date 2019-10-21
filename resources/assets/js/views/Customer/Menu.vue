<template>
  <div>
    <auth-modal :showAuthModal="showAuthModal"></auth-modal>

    <category-slider></category-slider>

    <div class="menu">
      <store-description-modal
        :showDescriptionModal="showDescriptionModal"
      ></store-description-modal>

      <meal-components-modal ref="componentModal"></meal-components-modal>

      <meal-package-components-modal
        ref="packageComponentModal"
      ></meal-package-components-modal>

      <meal-filter-modal
        :viewFilterModal="viewFilterModalParent"
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
        <div :class="`col-md-12 main-menu-area`">
          <!--<Spinner
            v-if="!meals.length && !mealPackages.length"
            position="absolute"
          />!-->

          <store-closed
            v-if="!$route.params.storeView"
            :storeView="storeView"
          ></store-closed>
          <outside-delivery-area
            v-if="!$route.params.storeView"
            :storeView="storeView"
          ></outside-delivery-area>
          <!--<meals-area
            :meals="mealsMix"
            :card="card"
            :cardBody="cardBody"
            :resetMeal="resetMeal"
            @onCategoryVisible="onCategoryVisible($event)"
            @showMealModal="showMealModal"
          ></meals-area>!-->

          <meals-area
            :meals="mealsMix"
            :card="card"
            :cardBody="cardBody"
            :resetMeal="resetMeal"
            :filters="filters"
            :search="search"
            :filteredView="filteredView"
            @onCategoryVisible="onCategoryVisible($event)"
          ></meals-area>

          <meal-page
            :meal="meal"
            :slickOptions="slickOptions"
            :storeSettings="storeSettings"
            :mealDescription="mealDescription"
            :ingredients="ingredients"
            :nutritionalFacts="nutritionalFacts"
          ></meal-page>

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

          <!--<meal-packages-area :mealPackages="mealPackages"></meal-packages-area>!-->
        </div>

        <div class="categoryNavArea" v-if="!mobile">
          <!-- <div class="categoryNavArea_header">
            <h3 class="white-text d-inline pr-2">Category</h3>
          </div> -->

          <div class="categoryNavArea_body">
            <div class="categoryNavArea_body_inner">
              <div class="row">
                <div class="col-md-1">
                  <img
                    src="/images/customer/x.png"
                    @click="search = ''"
                    class="clear-meal ml-2"
                  />
                </div>
                <div class="col-md-11">
                  <b-form-textarea
                    v-model="search"
                    placeholder="Search"
                    class="meal-search center-text mb-4"
                  ></b-form-textarea>
                </div>
              </div>
              <div
                v-for="(cat, index) in finalCategories"
                :key="cat.name"
                :class="
                  index == 0 ? 'categoryNavItem active' : 'categoryNavItem'
                "
                :target="'categorySection_' + cat.id"
              >
                {{ cat.name }}
              </div>
            </div>
            <!-- Inner Body End !-->
          </div>
        </div>

        <div :class="showBagClass" v-if="!mobile">
          <bag-area
            :manualOrder="manualOrder"
            :adjustOrder="adjustOrder"
            :adjustMealPlan="adjustMealPlan"
            :subscriptionId="subscriptionId"
            :orderId="orderId"
            :storeView="storeView"
          >
          </bag-area>
          <div class="bag-bottom-area">
            <bag-actions
              :storeView="storeView"
              :manualOrder="manualOrder"
              :checkoutData="checkoutData"
              :forceValue="forceValue"
              :adjustOrder="adjustOrder"
              :adjustMealPlan="adjustMealPlan"
              :subscriptionId="subscriptionId"
              :preview="preview"
              :orderId="orderId"
              :deliveryDay="deliveryDay"
              :transferTime="transferTime"
              :pickup="pickup"
              :order="order"
              :inSub="inSub"
            ></bag-actions>
          </div>
        </div>
        <div :class="showFilterClass">
          <menu-filters> ></menu-filters>
        </div>
      </div>
    </div>
    <v-style> .categoryNavItem.active {color: {{ brandFontColor }} } </v-style>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import nutritionFacts from "nutrition-label-jquery-plugin";
import Spinner from "../../components/Spinner";
import MealVariationsArea from "../../components/Modals/MealVariationsArea";
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
import MealPage from "../../components/Customer/MealPage";
import { sidebarCssClasses } from "../../shared/classes";

window.addEventListener("hashchange", function() {
  window.scrollTo(window.scrollX, window.scrollY - 500);
});

$(function() {
  var byPassScroll = false;
  $("body").on("click", ".categoryNavItem", function() {
    if ($(this).hasClass("active")) {
      return;
    }

    let target = $(this).attr("target");
    if (!target) {
      return;
    }

    byPassScroll = true;

    $(".categoryNavItem").removeClass("active");
    $(this).addClass("active");

    $([document.documentElement, document.body]).animate(
      {
        scrollTop:
          $(".categorySection[target='" + target + "']").offset().top - 89
      },
      700
    );

    setTimeout(() => {
      byPassScroll = false;
    }, 800);
  });

  $(window).on("scroll", function() {
    buildCategoryScroll();
  });

  function buildCategoryScroll() {
    if (byPassScroll) {
      return;
    }

    let windowScroll = $(window).scrollTop();

    $(".categorySection").each(function() {
      if (windowScroll >= $(this).offset().top - 90) {
        let target = $(this).attr("target");
        $(".categoryNavItem").removeClass("active");
        $('.categoryNavItem[target="' + target + '"]').addClass("active");
      }
    });
  }
});

export default {
  components: {
    Spinner,
    SalesTax,
    LightBox,
    Carousel,
    Slide,
    MealVariationsArea,
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
    MenuFilters,
    MealPage,
    MealComponentsModal
  },
  mixins: [MenuBag],
  props: {
    storeView: false,
    preview: false,
    manualOrder: false,
    forceValue: false,
    checkoutData: null,
    adjustOrder: false,
    adjustMealPlan: false,
    order: {},
    subscription: {},
    subscriptionId: null,
    orderId: null,
    order: null,
    deliveryDay: null,
    transferTime: null,
    pickup: null,
    inSub: null
  },
  data() {
    return {
      resetMeal: false,
      showBagClass: "shopping-cart show-right bag-area d-none",
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
      viewFilterModalParent: false,
      showDescriptionModal: false,
      filteredView: false,
      filters: {
        tags: [],
        allergies: [],
        categories: []
      },
      finalCategories: [],
      meal: null,
      mealPackage: null,
      ingredients: "",
      mealDescription: "",
      mealModal: false,
      mealPageView: false,
      mealPackageModal: false,
      nutritionalFacts: {},
      showMealsArea: true,
      showMealPackagesArea: true,
      mealSizePrice: null
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      context: "context",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      _categories: "viewedStoreCategories",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      allTags: "tags"
    }),
    mealsMix() {
      if (this.context == "customer" || this.context == "guest") {
        return [];
      }

      let meals = this.store.meals;
      let packages = this.store.packages;
      let filters = this.filters;
      let grouped = {};

      if (!_.isArray(meals)) {
        meals = [];
      }
      if (!_.isArray(packages)) {
        packages = [];
      }

      const search = this.search.toLowerCase();

      meals = _.filter(meals, meal => {
        if (
          !meal.active ||
          (this.search && !meal.title.toLowerCase().includes(search))
        ) {
          return false;
        }
        return true;
      });

      packages = _.map(
        _.filter(this.store.packages, mealPackage => {
          return (
            mealPackage.active &&
            (!this.search || mealPackage.title.toLowerCase().includes(search))
          );
        }) || [],
        mealPackage => {
          mealPackage.meal_package = true;
          return mealPackage;
        }
      );

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

      let total = meals.concat(packages);

      total.forEach(meal => {
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

      /* Sort Categories */
      let sortedCategories = [];

      for (let i = 0; i < this._categories.length; i++) {
        let cat = this._categories[i];
        let order = !isNaN(cat.order) ? parseInt(cat.order) : 9999;

        sortedCategories.push({
          name: cat.category,
          order,
          id: cat.id
        });
      }

      if (sortedCategories.length > 1) {
        for (let i = 0; i < sortedCategories.length - 1; i++) {
          for (let j = i + 1; j < sortedCategories.length; j++) {
            if (sortedCategories[i].order > sortedCategories[j].order) {
              let temp = {
                ...sortedCategories[i]
              };
              sortedCategories[i] = {
                ...sortedCategories[j]
              };
              sortedCategories[j] = {
                ...temp
              };
            }
          }
        }
      }
      /* Sort Categories End */

      let finalData = [];
      let finalCategories = [];

      for (let i = 0; i < sortedCategories.length; i++) {
        let name = sortedCategories[i].name;
        let order = sortedCategories[i].order;
        let category_id = sortedCategories[i].id;

        if (grouped[name] && grouped[name].length > 0) {
          finalData.push({
            category: name,
            category_id,
            meals: grouped[name],
            order
          });

          finalCategories.push(sortedCategories[i]);
        }
      }

      this.finalCategories = finalCategories;
      return finalData;
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
    tags() {
      let grouped = [];
      if (this.allTags && this.allTags.length > 0) {
        this.allTags.forEach(tag => {
          if (!_.includes(grouped, tag.tag)) {
            grouped.push(tag.tag);
          }
        });
      }

      return grouped;
    },
    showIngredients() {
      return this.storeSettings.showIngredients;
    },
    menuPage() {
      if (this.$route.name === "customer-menu") return true;
      else return false;
    },
    brandColor() {
      let style = "background-color:";
      style += this.store.settings.color;
      return style;
    },
    brandFontColor() {
      return this.store.settings.color;
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
    this.$eventBus.$on("backToMenu", () => {
      this.backToMenu();
    });
  },
  mounted() {
    if (this.bag.length > 0) {
      this.showBagClass = "shopping-cart show-right bag-area";
    } else this.showBagClass = "shopping-cart hidden-right bag-area";

    if (this.storeView) {
      /* Sidebar Check */
      let isOpen = false;

      for (let i in sidebarCssClasses) {
        if ($("body").hasClass(sidebarCssClasses[i])) {
          isOpen = true;
          break;
        }
      }

      if (isOpen && $(".navbar-toggler").length > 0) {
        $(".navbar-toggler").click();
      }
      /* Sidebar Check End */

      if (this.$route.params.storeView)
        this.showBagClass = "shopping-cart show-right bag-area";
      else this.showBagClass = "shopping-cart show-right bag-area";
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
    // showMealModal(meal) {
    //   this.meal = meal;
    //   this.mealModal = true;
    //   this.mealDescription = meal.description
    //     ? meal.description.replace(/\n/g, "<br>")
    //     : "";
    //     this.getNutritionFacts(this.meal.ingredients, this.meal);
    //     // this.$refs.mealGallery.reSlick();
    // },
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
    showMealPage(meal) {
      this.mealPageView = true;
      this.meal = meal;
      this.mealDescription = meal.description
        ? meal.description.replace(/\n/g, "<br>")
        : "";

      let sortedIngredients = this.meal.ingredients.sort((a, b) => {
        return b.pivot.quantity_base - a.pivot.quantity_base;
      });
      this.$nextTick(() => {
        this.getNutritionFacts(sortedIngredients, this.meal);
        // this.$refs.mealGallery.reSlick();
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
      const servingsPerMeal = this.meal.servingsPerMeal;
      const servingSizeUnit = this.meal.servingSizeUnit;
      this.nutritionalFacts = {
        showItemName: false,
        showServingUnitQuantity: true,
        valueServingPerContainer: servingsPerMeal,
        valueServingUnitQuantity: 1,
        valueServingSizeUnit: servingSizeUnit,
        showServingsPerContainer: true,

        itemName: meal.title,
        ingredientList: ingredientList,
        showIngredients: this.showIngredients,
        decimalPlacesForQuantityTextbox: 2,
        allowFDARounding: false,
        decimalPlacesForNutrition: 0,
        showPolyFat: false,
        showMonoFat: false,
        valueCalories: nutrition.calories / servingsPerMeal,
        valueFatCalories: nutrition.fatCalories / servingsPerMeal,
        valueTotalFat: nutrition.totalFat / servingsPerMeal,
        valueSatFat: nutrition.satFat / servingsPerMeal,
        valueTransFat: nutrition.transFat / servingsPerMeal,
        valueCholesterol: nutrition.cholesterol / servingsPerMeal,
        valueSodium: nutrition.sodium / servingsPerMeal,
        valueTotalCarb: nutrition.totalCarb / servingsPerMeal,
        valueFibers: nutrition.fibers / servingsPerMeal,
        valueSugars: nutrition.sugars / servingsPerMeal,
        valueProteins: nutrition.proteins / servingsPerMeal,
        valueVitaminD: ((nutrition.vitaminD / 20000) * 100) / servingsPerMeal,
        valuePotassium_2018:
          ((nutrition.potassium / 4700) * 100) / servingsPerMeal,
        valueCalcium: ((nutrition.calcium / 1300) * 100) / servingsPerMeal,
        valueIron: ((nutrition.iron / 18) * 100) / servingsPerMeal,
        valueAddedSugars: nutrition.addedSugars / servingsPerMeal,
        showLegacyVersion: false
      };
    },
    showBag() {
      if (this.storeView) return;
      if (this.showBagClass.includes("hidden-right")) {
        this.showBagClass = "d-inline shopping-cart show-right bag-area";
        if (this.menuPage) {
          //this.showBagClass += " area-scroll";
        }
      } else if (this.showBagClass.includes("show-right")) {
        this.showBagClass = "shopping-cart hidden-right bag-area";
        if (this.menuPage) {
          //this.showBagClass += " area-scroll";
        }
      }
    },
    showFilterArea() {
      this.viewFilterModalParent = true;

      // Hiding left pop out filter area now that categories are added in.

      // if (this.showFilterClass === "shopping-cart hidden-left bag-area")
      //   this.showFilterClass = "shopping-cart show-left bag-area";
      // else if (this.showFilterClass === "shopping-cart show-left bag-area")
      //   this.showFilterClass = "shopping-cart hidden-left bag-area";
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
    backToMenu() {
      this.showMealsArea = true;
      this.showMealPackagesArea = true;
      this.mealPageView = false;
    }
  }
};
</script>
