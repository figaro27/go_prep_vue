<template>
  <div>
    <auth-modal :showAuthModal="showAuthModal"></auth-modal>

    <LightBox
      ref="lightbox"
      :images="galleryImages"
      :showLightBox="false"
    ></LightBox>

    <category-slider ref="categorySlider"></category-slider>

    <div class="menu">
      <!-- <delivery-date-modal
        v-if="!bagDeliveryDate && deliveryDateRequired"
      ></delivery-date-modal> -->

      <!-- Temp fix to make the modal show -->
      <delivery-date-modal
        v-if="
          store.modules.category_restrictions &&
            !$route.params.storeView &&
            !storeView &&
            context !== 'store' &&
            !$route.params.backFromBagPage
        "
      ></delivery-date-modal>

      <zip-code-modal
        v-if="
          store.delivery_day_zip_codes &&
            store.delivery_day_zip_codes.length > 0 &&
            transferTypes.delivery &&
            store.modules.multipleDeliveryDays &&
            ((!loggedIn && context !== 'store') || context == 'store') &&
            !$route.params.orderId &&
            !adjustMealPlan
        "
        @setAutoPickUpcomingMultDD="autoPickUpcomingMultDD(null)"
      ></zip-code-modal>

      <store-description-modal
        :showDescriptionModal="showDescriptionModal"
      ></store-description-modal>

      <meal-components-modal ref="componentModal"></meal-components-modal>

      <b-modal
        v-model="showVariationsModal"
        v-if="showVariationsModal"
        size="sm"
        no-fade
        hide-header
        hide-footer
      >
        <div class="d-flex d-center">
          <meal-variations-area
            :meal="meal"
            :sizeId="sizeId"
            :fromMealsArea="true"
            @closeVariationsModal="showVariationsModal = false"
          ></meal-variations-area>
        </div>
      </b-modal>

      <!--<meal-package-components-modal
        ref="packageComponentModal"
      ></meal-package-components-modal>!-->
      <b-modal
        size="md"
        cancel-disabled
        v-model="showDeliveryDayModal"
        hide-footer
        hide-header
        no-fade
      >
        <div class="row mt-3">
          <div class="col-md-12">
            <div style="position: relative">
              <center>
                <b-form-radio-group
                  v-if="isMultipleDelivery && hasBothTranserTypes"
                  buttons
                  class="storeFilters mb-3"
                  v-model="bagPickup"
                  :options="[
                    { value: 1, text: 'Pickup' },
                    { value: 0, text: 'Delivery' }
                  ]"
                  @change="val => changeTransferType(val)"
                ></b-form-radio-group>
              </center>

              <h4 class="center-text">Select Day</h4>

              <Spinner
                v-if="isLoadingDeliveryDays"
                position="relative"
                style="left: 0;"
              />
              <div class="delivery_day_wrap mt-3">
                <div
                  @click="changeDeliveryDay(day)"
                  v-for="day in sortedDeliveryDays"
                  v-bind:class="
                    selectedDeliveryDay &&
                    selectedDeliveryDay.day_friendly == day.day_friendly
                      ? 'delivery_day_item active'
                      : 'delivery_day_item'
                  "
                  :style="getBrandColor(day)"
                >
                  {{ moment(day.day_friendly).format("dddd, MMM Do YYYY") }}
                </div>
                <div
                  v-if="
                    sortedDeliveryDays.length === 0 &&
                      store.delivery_day_zip_codes.length > 0
                  "
                >
                  <center>
                    <!-- <b-alert style="background-color:#EBFAFF" show>
                      <p>
                        Sorry, there are no delivery days available for your zip
                        code.
                      </p>
                    </b-alert> -->
                    <zip-code-modal :deliverySelected="true"></zip-code-modal>
                  </center>
                </div>
              </div>

              <!-- <div class="delivery_day_wrap mt-3">
                <div
                  @click="changeDeliveryDay(delivery_day)"
                  v-bind:class="
                    selectedDeliveryDay &&
                    selectedDeliveryDay.id == delivery_day.id
                      ? 'delivery_day_item active'
                      : 'delivery_day_item'
                  "
                  :style="getBrandColor(delivery_day)"
                  v-for="(delivery_day, index) in store.delivery_days"
                  v-bind:key="index"
                >
                  {{
                    moment(delivery_day.day_friendly).format(
                      "dddd, MMM Do YYYY"
                    )
                  }}
                </div>
              </div> -->
            </div>
            <!-- Relative End !-->
          </div>
        </div>
        <!-- Row End !-->
      </b-modal>

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

      <b-modal
        size="lg"
        title="Add Meal To"
        v-model="adjustMealModal"
        v-if="adjustMealModal"
        @hide="adjustMealModal = false"
        @ok.prevent="e => ok(e)"
        no-fade
      >
        <div class="row mt-3">
          <div class="col-md-12">
            <b-radio-group
              v-model="adjustMealModal_index"
              stacked
              @input.native="e => e"
            >
              <b-radio
                v-for="(option, index) in adjustMealModal_items"
                :value="index"
                v-bind:key="'adjustMealPackage_' + index"
              >
                {{ option.meal.title }}
                <span v-if="option.size && option.size.title !== 'Regular'">
                  - {{ option.size.title }}
                </span>
              </b-radio>

              <b-radio :value="adjustMealModal_items.length">
                Bag
              </b-radio>
            </b-radio-group>
          </div>
        </div>
      </b-modal>

      <div class="row">
        <div :class="`col-md-12 main-menu-area menu-page`">
          <Spinner v-if="showSpinner || forceShow" position="fixed" />

          <!--<meals-area
            :meals="mealsMix"
            :card="card"
            :cardBody="cardBody"
            @onCategoryVisible="onCategoryVisible($event)"
            @showMealModal="showMealModal"
          ></meals-area>!-->

          <meals-area
            :meals="mealsMix"
            :card="card"
            :cardBody="cardBody"
            :filters="filters"
            :search="search"
            :filteredView="filteredView"
            :adjustOrder="adjustOrder"
            :manualOrder="manualOrder"
            @onCategoryVisible="onCategoryVisible($event)"
            @showVariations="showVariations($event)"
          ></meals-area>

          <meal-page
            :meal="meal"
            :slickOptions="slickOptions"
            :storeSettings="storeSettings"
            :mealDescription="mealDescription"
            :ingredients="ingredients"
            :nutritionalFacts="nutritionalFacts"
            :adjustOrder="adjustOrder"
            :manualOrder="manualOrder"
            @show-gallery="showGallery"
            ref="mealPage"
          ></meal-page>

          <meal-package-page
            :mealPackage="mealPackage"
            :mealPackageSize="mealPackageSize"
            :storeSettings="storeSettings"
            :storeView="storeView"
            ref="mealPackagePage"
          ></meal-package-page>

          <div @click="backFromPackagePage" style="margin-bottom:20px">
            <floating-action-button
              class="d-md-none"
              style="background-color:#808080"
              v-if="mealPackagePageView"
            >
              <div class="d-flex flex-column h-100">
                <i class="fas fa-arrow-circle-left text-white"></i>
                <i v-if="total" class="text-white mt-1"></i>
              </div>
            </floating-action-button>
          </div>

          <!-- <floating-action-button
            class="d-md-none"
            :style="brandColor"
            :to="bagPageURL"
            v-if="(!subscriptionId || !adjustOrder) && !mealPackagePageView"
          >
            <div class="d-flex flex-column h-100">
              <i class="fa fa-shopping-cart text-white"></i>
              <i v-if="total" class="text-white mt-1">{{ total }}</i>
            </div>
          </floating-action-button> -->

          <!-- <floating-action-area
            class="d-md-none"
            :style="remainingTextStyle"
            :to="bagPageURL"
            v-if="
              (!subscriptionId || !adjustOrder) &&
                !mealPackagePageView &&
                !mealPageView
            "
          >
            <div
              class="d-flex flex-column pl-1 pr-1"
              style="border-radius:10px"
            >
              <p
                class="pt-2"
                v-if="minOption === 'price' && minPrice > totalBagPricePreFees"
              >
                {{
                  format.money(
                    minPrice - totalBagPricePreFees,
                    storeSettings.currency
                  )
                }}
                Remaining
              </p>
              <p class="pt-2" v-if="minOption === 'meals' && minMeals > total">
                {{ minMeals - total }} {{ items }} Remaining
              </p>
            </div>
          </floating-action-area> -->

          <button
            v-if="!mealPackagePageView && !mealPageView && mobile"
            type="button"
            :style="brandColor"
            class="mobile-sticky-button btn btn-lg"
            @click="goToCheckout"
          >
            <div
              class="d-flex flex-column pl-1 pr-1"
              style="border-radius:10px"
            >
              <span v-if="!minimumMet">
                <p class="pt-2 white-text">
                  {{ addMore }}
                </p>
              </span>
              <span v-else>
                <p class="pt-2 white-text font-16" v-if="!adjustingScreen">
                  Continue To Checkout
                </p>
                <p class="pt-2 white-text font-16" v-else>Continue</p>
              </span>
            </div>
          </button>

          <div style="margin-right:65px;margin-bottom:20px">
            <floating-action-button
              class="d-md-none"
              :style="brandColor"
              v-if="
                store.modules.multipleDeliveryDays &&
                  finalDeliveryDay &&
                  !mealPackagePageView
              "
            >
              <div
                class="d-flex flex-column h-100"
                @click="showDeliveryDayModal = true"
              >
                <i class="fas fa-truck text-white"></i>
                <!-- <span class="text-white mt-1" v-if="finalDeliveryDay">{{
                moment(finalDeliveryDay.day_friendly).format("ddd")
              }}</span> -->
              </div>
            </floating-action-button>
          </div>

          <!--<meal-packages-area :mealPackages="mealPackages"></meal-packages-area>!-->
        </div>

        <div class="categoryNavArea" v-if="!mobile">
          <!-- <div class="categoryNavArea_header">
            <h3 class="white-text d-inline pr-2">Category</h3>
          </div> -->

          <div class="categoryNavArea_body">
            <div class="categoryNavArea_body_inner">
              <div class="row">
                <div class="col-md-2">
                  <i
                    class="fas fa-times-circle clear-meal dark-gray pt-1"
                    @click="search = ''"
                  ></i>
                </div>
                <div class="col-md-10">
                  <b-form-textarea
                    v-model="search"
                    placeholder="Search"
                    class="meal-search center-text mb-4"
                  ></b-form-textarea>
                </div>
              </div>

              <div v-if="finalCategoriesSub && finalCategoriesSub.length > 0">
                <div
                  v-for="(cat, index) in finalCategoriesSub"
                  v-if="isCategoryVisible(cat) && hasItems(null, cat)"
                  :key="'com_' + cat.id"
                  :class="
                    index == 0 ? 'categoryNavItem active' : 'categoryNavItem'
                  "
                  :target="'categorySection_' + cat.id"
                >
                  {{ cat.title }}
                </div>
              </div>

              <div v-else>
                <div
                  v-for="(cat, index) in finalCategories"
                  v-if="
                    isCategoryVisible(cat) && cat.visible && hasItems(null, cat)
                  "
                  :key="cat.category"
                  :class="
                    index == 0 ? 'categoryNavItem active' : 'categoryNavItem'
                  "
                  @click="backToMenu('categorySection_' + cat.id, 700)"
                  :target="'categorySection_' + cat.id"
                >
                  {{ cat.category }}
                </div>
              </div>
              <div class="row d-inline" v-if="mealPackagePageView">
                <div class="col-md-12 center-text mb-3">
                  <button
                    @click="backFromPackagePage"
                    type="button"
                    class="btn btn-secondary btn-lg d-inline"
                  >
                    Back
                  </button>
                  <button
                    @click="addFromPackagePage"
                    type="button"
                    class="btn btn-secondary btn-lg brand-color white-text d-inline"
                  >
                    Add
                  </button>
                </div>
              </div>
              <div v-if="mealPackagePageView">
                <h4 class="center-text mt-2" v-if="totalRemainingMeals > 0">
                  Remaining: {{ totalRemainingMeals }}
                </h4>
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
            :selectedDeliveryDay="selectedDeliveryDay"
            @changeDeliveryDay="changeDeliveryDay($event)"
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
              :staffMember="staffMember"
              :order="order"
              :inSub="inSub"
              :weeklySubscriptionValue="weeklySubscriptionValue"
              :lineItemOrders="lineItemOrders"
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
import DeliveryDateModal from "./Modals/DeliveryDateModal";
import ZipCodeModal from "./Modals/ZipCodeModal";
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
import MealPackagePage from "../../components/Customer/MealPackagePage";
import { sidebarCssClasses } from "../../shared/classes";
import store from "../../store";
import auth from "../../lib/auth";

window.addEventListener("hashchange", function() {
  window.scrollTo(window.scrollX, window.scrollY - 500);
});

let scrollToCategory;

$(function() {
  var byPassScroll = false;

  scrollToCategory = (target, speed = 700) => {
    byPassScroll = true;

    $(".categoryNavItem").removeClass("active");

    $([document.documentElement, document.body]).animate(
      {
        scrollTop:
          $(".categorySection[target='" + target + "']").offset().top - 100
      },
      speed
    );

    setTimeout(() => {
      byPassScroll = false;
    }, 800);

    $('.categoryNavItem[target="' + target + '"]').addClass("active");
  };

  $("body").on("click", ".categoryNavItem", function() {
    if ($(this).hasClass("active")) {
      return;
    }

    let target = $(this).attr("target");
    if (!target) {
      return;
    }

    if ($(".categorySection[target='" + target + "']").length == 0) {
      return;
    }
    scrollToCategory(target);
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
    MealPackagePage,
    MealComponentsModal,
    DeliveryDateModal,
    ZipCodeModal,
    MealVariationsArea
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
    staffMember: null,
    inSub: null,
    weeklySubscriptionValue: null,
    lineItemOrders: null
  },
  data() {
    return {
      totalRemainingMeals: 0,
      activeCatId: 0,
      showVariationsModal: false,
      bagPageURL: "/customer/bag",
      adjustMealModal: false,
      adjustMealModal_meal: null,
      adjustMealModal_size: null,
      adjustMealModal_special_instructions: null,
      adjustMealModal_items: [],
      adjustMealModal_components: null,
      adjustMealModal_addons: null,
      adjustMealModal_index: 0,
      showBagClass: "shopping-cart show-right bag-area",
      showFilterClass: "shopping-cart hidden-left bag-area",
      search: "",
      showAuthModal: false,
      showDeliveryDayModal: false,
      selectedDeliveryDay: null,
      finalDeliveryDay: null,
      slickOptions: {
        slidesToShow: 4,
        infinite: false,
        arrows: true,
        prevArrow:
          '<a class="slick-prev"><i class="fa fa-chevron-left"></i></a>',
        nextArrow:
          '<a class="slick-next"><i class="fa fa-chevron-right"></i></a>',
        variableWidth: true
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
      finalCategoriesSub: [],
      meal: null,
      mealPackage: null,
      mealPackageSize: null,
      ingredients: "",
      mealDescription: "",
      mealModal: false,
      mealPageView: false,
      mealPackagePageView: false,
      mealPackagePageComponents: null,
      mealPackageModal: false,
      nutritionalFacts: {},
      showMealsArea: true,
      showMealPackagesArea: true,
      mealSizePrice: null,
      forceShow: false,
      deliveryDate: null,
      galleryImages: [],
      sizeId: null
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      context: "context",
      isLazy: "isLazy",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      mealMixItems: "mealMixItems",
      _categories: "viewedStoreCategories",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      allTags: "tags",
      bagDeliveryDate: "bagDeliveryDate",
      bagPickup: "bagPickup",
      bagZipCode: "bagZipCode",
      loggedIn: "loggedIn",
      user: "user",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      minOption: "minimumOption",
      totalBagPricePreFees: "totalBagPricePreFees"
    }),
    adjustingScreen() {
      if (
        this.adjustOrder ||
        this.$route.params.adjustOrder ||
        this.adjustMealPlan ||
        this.$route.params.adjustMealPlan ||
        this.$route.query.sub === "true" ||
        this.subscriptionId
      ) {
        return true;
      } else {
        return false;
      }
    },
    hasBothTranserTypes() {
      let hasPickup = false;
      let hasDelivery = false;
      this.store.delivery_days.forEach(day => {
        if (day.type === "delivery") {
          hasDelivery = true;
        }
        if (day.type === "pickup") {
          hasPickup = true;
        }
      });
      if (hasPickup && hasDelivery) {
        return true;
      } else {
        return false;
      }
    },
    transferTypes() {
      let hasDelivery = false;
      let hasPickup = false;

      this.store.delivery_days.forEach(day => {
        if (day.type == "delivery") {
          hasDelivery = true;
        }
        if (day.type == "pickup") {
          hasPickup = true;
        }
      });

      let hasBoth = hasDelivery && hasPickup ? true : false;

      return {
        delivery: hasDelivery,
        pickup: hasPickup,
        both: hasBoth
      };
    },
    remainingTextStyle() {
      if (this.isMultipleDelivery) {
        return "margin-right:65px";
      }
    },
    items() {
      if (this.minMeals - this.total > 1) {
        return "items";
      }
      return "item";
    },
    sortedDeliveryDays() {
      // If delivery_days table has the same day of the week for both pickup & delivery, only show the day once
      let baseDeliveryDays = this.store.delivery_days;
      let deliveryWeeks = this.store.settings.deliveryWeeks;
      let storeDeliveryDays = [];

      for (let i = 0; i <= deliveryWeeks; i++) {
        baseDeliveryDays.forEach(day => {
          let m = moment(day.day_friendly);
          let newDate = moment(m).subtract(i, "week");
          let newDay = { ...day };
          newDay.day_friendly = newDate.format("YYYY-MM-DD");
          storeDeliveryDays.push(newDay);
        });
      }

      storeDeliveryDays = storeDeliveryDays.reverse();

      // Add all future dates with no cutoff for manual orders
      if (this.context == "store") {
        storeDeliveryDays = [];
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();
        let date = today.getDate();

        for (let i = 0; i < 30; i++) {
          let day = new Date(year, month, date + i);
          let multDD = { ...this.store.delivery_days[0] };
          multDD.day_friendly = moment(day).format("YYYY-MM-DD");
          storeDeliveryDays.push(multDD);
        }
      }

      let sortedDays = storeDeliveryDays;
      // let sortedDays = [];

      // if (this.store.delivery_day_zip_codes.length === 0) {
      //   sortedDays = _.uniqBy(storeDeliveryDays, "day_friendly");
      // } else {
      //   sortedDays = storeDeliveryDays;
      // }

      // If the store only serves certain zip codes on certain delivery days
      if (this.store.delivery_day_zip_codes.length > 0) {
        let deliveryDayIds = [];
        this.store.delivery_day_zip_codes.forEach(ddZipCode => {
          if (ddZipCode.zip_code === parseInt(this.bagZipCode)) {
            deliveryDayIds.push(ddZipCode.delivery_day_id);
          }
        });
        sortedDays = sortedDays.filter(day => {
          if (this.bagPickup) {
            return true;
          } else {
            if (deliveryDayIds.includes(day.id) && day.type == "delivery") {
              return true;
            }
          }

          // return deliveryDayIds.includes(day.id);
        });
      }

      if (this.context !== "store") {
        if (this.bagPickup) {
          sortedDays = sortedDays.filter(day => {
            return day.type === "pickup";
          });
        } else {
          sortedDays = sortedDays.filter(day => {
            return day.type === "delivery";
          });
        }
      }

      sortedDays.sort(function(a, b) {
        return new Date(a.day_friendly) - new Date(b.day_friendly);
      });

      // Removing past dates
      sortedDays = sortedDays.filter(day => {
        return !moment(day.day_friendly).isBefore(moment().startOf("day"));
      });

      return sortedDays;
    },
    isMultipleDelivery() {
      return this.store.modules.multipleDeliveryDays == 1 ? true : false;
    },
    isLoadingDeliveryDays() {
      if (!this.store.delivery_days || this.store.delivery_days.length == 0) {
        return true;
      }
      return false;
    },
    deliveryDateRequired() {
      return this.hasDeliveryDateRestriction;
    },
    showSpinner() {
      const { items } = this.mealMixItems;

      if (!items || items.length == 0 || this.finalCategories.length == 0) {
        return true;
      }

      return false;
    },
    meals() {
      return this.store.meals;
    },
    mealPackages() {
      return this.store.packages;
    },
    mealsMix() {
      const search = this.search.toLowerCase();
      let filters = this.filters;

      let { items, finalCategories } = this.mealMixItems;

      this.finalCategories = finalCategories;

      const categoryIds = [];

      items = items.map(item => {
        let object = { ...item };

        object.meals = _.filter(object.meals, meal => {
          if (
            !meal.active ||
            (this.search && !meal.title.toLowerCase().includes(search))
          ) {
            return false;
          }

          return true;
        });

        if (object.meals && object.meals.length > 0) {
          if (!categoryIds.includes(object.category_id)) {
            categoryIds.push(object.category_id);
          }
        }

        return object;
      });

      if (this.filteredView) {
        items = items.map(item => {
          let object = { ...item };

          object.meals = _.filter(object.meals, meal => {
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

          if (object.meals && object.meals.length > 0) {
            if (!categoryIds.includes(object.category_id)) {
              categoryIds.push(object.category_id);
            }
          }

          return object;
        });
      }

      /* Categories */
      // const finalCategories = [];
      /*if (this.store.finalCategories) {
        this.store.finalCategories.forEach(category => {
          if (categoryIds.includes(category.id)) {
            finalCategories.push(category);
          }
        });
      }

      this.finalCategories = finalCategories;*/
      /* Categories End */

      return items;

      /* Disabled Old Workflow */
      /*
      let meals = this.store.meals;
      let packages = this.store.packages;
      let grouped = {};

      if (!_.isArray(meals)) {
        meals = [];
      }
      if (!_.isArray(packages)) {
        packages = [];
      }

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

      // Sort Categories
      let sortedCategories = [];

      for (let i = 0; i < this._categories.length; i++) {
        let cat = this._categories[i];
        let order = !isNaN(cat.order) ? parseInt(cat.order) : 9999;

        sortedCategories.push({
          category: cat.category,
          subtitle: cat.subtitle,
          order,
          id: cat.id,
          cat
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
      // Sort Categories End

      let finalData = [];
      let finalCategories = [];

      for (let i = 0; i < sortedCategories.length; i++) {
        let name = sortedCategories[i].category;
        let order = sortedCategories[i].order;
        let category_id = sortedCategories[i].id;
        let subtitle = sortedCategories[i].subtitle;

        if (grouped[name] && grouped[name].length > 0) {
          finalData.push({
            category: name,
            subtitle,
            category_id,
            meals: grouped[name],
            order
          });

          finalCategories.push(sortedCategories[i]);
        }
      }

      this.finalCategories = finalCategories;
      return finalData;
      */
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
    },
    query() {
      return this.$route.query;
    }
  },
  created() {
    // Check for auth token in URL
    const { tkn, tknexp } = this.$route.query;
    if (tkn) {
      auth.setToken({
        access_token: tkn,
        expires_in: Number.parseInt(tknexp)
      });
      // Refresh
      window.location.replace(window.location.href.split("?")[0]);
    }

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
    if (this.store.modules.pickupOnly && this.context !== "store") {
      this.setBagPickup(1);
    }
    if (this.store.modules.multipleDeliveryDays) {
      if (!this.transferTypes.delivery && this.transferTypes.pickup) {
        this.setBagPickup(1);
      }
      if (this.transferTypes.delivery && !this.transferTypes.pickup) {
        this.setBagPickup(0);
      }
      if (this.bagPickup === 1) {
        this.autoPickUpcomingMultDD();
      }
      if (this.store.delivery_day_zip_codes.length === 0) {
        this.autoPickUpcomingMultDD();
      } else {
        if (this.loggedIn && this.context !== "store") {
          this.setBagZipCode(parseInt(this.user.user_detail.zip));
          this.autoPickUpcomingMultDD(this.sortedDeliveryDays);
        }
        if (this.bagZipCode) {
          this.setBagZipCode(parseInt(this.bagZipCode));
          this.autoPickUpcomingMultDD(this.sortedDeliveryDays);
        }
      }
      if (this.sortedDeliveryDays.length > 0) {
        this.changeDeliveryDay(this.sortedDeliveryDays[0]);
      }
    }

    if (this.$route.query.sub || this.$route.query.subscriptionId) {
      this.bagPageURL =
        "/customer/bag?sub=true&subscriptionId=" + this.$route.params.id;
    }

    if (this.$route.query.filter) {
      this.search = this.$route.query.filter;
    }

    // if (this.bag.length > 0 || this.subscriptionId !== undefined) {
    //   this.showBagClass = "shopping-cart show-right bag-area";
    // } else this.showBagClass = "shopping-cart hidden-right bag-area";

    if (this.storeView || this.$route.params.storeView) {
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

      if (this.$route.params.storeView || this.storeView)
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
    if (!this.adjustMealPlan && !this.adjustOrder) {
      this.clearInactiveItems();
      this.removeOldDeliveryDates();
    }
  },
  beforeDestroy() {
    this.showActiveFilters();
  },
  watch: {
    query: function() {
      if (
        !("item" in this.query) &&
        !("package" in this.query) &&
        !("package_size" in this.query)
      ) {
        this.showMealsArea = true;
        this.mealPageView = false;
        this.mealPackagePageView = false;
      }
    },
    mealMixItems(val) {
      if (this.$route.query.cat && !val.isRunningLazy) {
        this.backToMenu("categorySection_" + this.$route.query.cat, 0);
      }
    }
  },
  methods: {
    showGallery(images, index) {
      this.galleryImages = images;
      this.$refs.lightbox.showImage(index);
    },
    ...mapActions([
      "initState",
      "refreshSubscriptions",
      "emptyBag",
      "refreshUpcomingOrders"
    ]),
    ...mapMutations([
      "emptyBag",
      "setBagMealPlan",
      "setBagCoupon",
      "setBagZipCode",
      "setBagPickup",
      "setMultDDZipCode"
    ]),
    updateScrollbar() {
      return; // disabling for now

      const isMobile = $("#xs:visible").length;

      if (!isMobile && !this.menuPs) {
        this.menuPs = new PerfectScrollbar(".main-menu-area:not(.ps)");
      } else if (!isMobile && this.menuPs) {
        this.menuPs.update();
      } else if (isMobile && this.menuPs) {
        this.menuPs.destroy();
        this.menuPs = null;
      }
    },
    changeDeliveryDay(e) {
      this.selectedDeliveryDay = e;
      this.finalDeliveryDay = e;
      this.showDeliveryDayModal = false;

      let dayIndex = moment(this.finalDeliveryDay.day_friendly).day();
      let type = this.finalDeliveryDay.type;

      let deliveryDay = this.store.delivery_days.find(day => {
        return day.day == dayIndex && day.type == type;
      });

      if (deliveryDay) {
        this.finalDeliveryDay.id = deliveryDay.id;
      }

      if (this.store.hasDeliveryDayItems) {
        e.has_items = true;
      }
      store.dispatch("refreshLazyDD", {
        delivery_day: this.finalDeliveryDay
      });
    },
    showAdjustModal(
      meal,
      size = null,
      components = null,
      addons = [],
      special_instructions = null,
      items = []
    ) {
      this.adjustMealModal = true;
      this.adjustMealModal_meal = this.getMeal(meal.id, meal);
      this.adjustMealModal_special_instructions = special_instructions;
      this.adjustMealModal_components = components;
      this.adjustMealModal_addons = addons;

      if (_.isObject(size) && size.id) {
        this.adjustMealModal_size = size;
      } else {
        if (size) {
          this.adjustMealModal_size = this.adjustMealModal_meal.getSize(size);
        } else {
          this.adjustMealModal_size = null;
        }
      }
      this.adjustMealModal_items = items;
      this.adjustMealModal_index = items.length;
    },
    ok() {
      this.adjustMealModal = false;
      const meal = this.adjustMealModal_meal;
      const size = this.adjustMealModal_size;
      const special_instructions = this.adjustMealModal_special_instructions;
      const items = this.adjustMealModal_items;
      const index = this.adjustMealModal_index;
      const addons = this.adjustMealModal_addons;
      const components = this.adjustMealModal_components;

      if (index != null && items && items[index]) {
        this.updateOneSubItemFromAdjust(
          {
            meal,
            size,
            special_instructions
          },
          items[index],
          true
        );
      } else if (index == items.length) {
        // Directly to Bag
        if (!addons) {
          addons = [];
        }

        this.addOne(
          meal,
          false,
          size,
          components,
          addons,
          special_instructions
        );
      }
      this.search = "";
      this.$refs.mealPage.back();
    },
    onCategoryVisible(isVisible, category) {
      if (isVisible && this.$refs.categorySlider) {
        this.$refs.categorySlider.goTo(category.category_id);
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
    async showMealPackagePage(meal, size) {
      this.mealPackage = meal;
      this.mealPackageSize = size;
      this.showMealsArea = false;
      this.mealPackagePageView = true;

      if (!size) {
        if (!("package" in this.$route.query)) {
          this.$router.push(this.$route.path + `?package=` + meal.id);
        }
      } else {
        if (!("size" in this.$route.query)) {
          this.$router.push(
            this.$route.path +
              `?package=` +
              meal.id +
              `&package_size=` +
              size.id
          );
        }
      }

      // Need to fix pushing packages to individual URLs first
      // if (!('package' in this.query)){
      //   if (!size){
      //     this.$router.push(`/customer/menu?package=` + this.slugifyItem(meal));
      //   }
      //   else {
      //     this.$router.push(`/customer/menu?package=` + this.slugifyItem(meal) + `&package_size=` + this.slugifyItem(size));
      //   }
      // }
    },
    async showMealPage(meal, size = null) {
      this.meal = meal;
      this.mealPageView = true;
      this.showMealsArea = false;
      // let title = this.slugifyItem(meal);
      let id = meal.id;
      if (!("item" in this.query)) {
        this.$router.push(this.$route.path + `?item=` + id);
        if (this.context !== "store") {
          axios.post("/api/addViewToMeal", {
            store_id: this.store.id,
            meal_id: id
          });
        }
      }
      this.$refs.mealPage.setSizeFromMealsArea(size);
      this.mealDescription = meal.description
        ? meal.description.replace(/\n/g, "<br>")
        : "";

      let sortedIngredients = this.meal.ingredients.sort((a, b) => {
        return b.pivot.quantity_base - a.pivot.quantity_base;
      });

      this.getNutritionFacts(sortedIngredients, this.meal);
      // this.$refs.mealGallery.reSlick();
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
    getNutritionFacts(ingredients, meal, ref = null, servingDetails = null) {
      const nutrition = this.nutrition.getTotals(ingredients);
      const ingredientList = this.nutrition.getIngredientList(ingredients);
      let servingsPerMeal = null;
      let servingSizeUnit = null;
      const servingUnitQuantity = meal.servingUnitQuantity
        ? meal.servingUnitQuantity
        : 1;
      if (servingDetails) {
        servingsPerMeal = servingDetails.servingsPerMeal;
        servingSizeUnit = servingDetails.servingSizeUnit;
      } else {
        servingsPerMeal = meal.servingsPerMeal;
        servingSizeUnit = meal.servingSizeUnit;
      }

      this.nutritionalFacts = {
        showItemName: false,
        showServingUnitQuantity: true,
        valueServingPerContainer: servingsPerMeal,
        valueServingUnitQuantity: servingUnitQuantity,
        valueServingSizeUnit: servingSizeUnit,
        showServingsPerContainer: true,
        showPolyFat: false,
        showMonoFat: false,
        showTransFat: false,
        showFibers: true,
        showVitaminD: false,
        showPotassium_2018: false,
        showCalcium: false,
        showIron: false,
        showCaffeine: false,
        itemName: meal.title,
        ingredientList: ingredientList,
        showIngredients: false,
        decimalPlacesForQuantityTextbox: 2,
        allowFDARounding: false,
        decimalPlacesForNutrition: 0,
        valueCalories: (nutrition.calories / servingsPerMeal).toFixed(0),
        valueFatCalories: (nutrition.fatcalories / servingsPerMeal).toFixed(0),
        valueTotalFat: (nutrition.totalfat / servingsPerMeal).toFixed(0),
        valueSatFat: (nutrition.satfat / servingsPerMeal).toFixed(0),
        valueTransFat: (nutrition.transfat / servingsPerMeal).toFixed(0),
        valueCholesterol: (nutrition.cholesterol / servingsPerMeal).toFixed(0),
        valueSodium: (nutrition.sodium / servingsPerMeal).toFixed(0),
        valueTotalCarb: (nutrition.totalcarb / servingsPerMeal).toFixed(0),
        valueFibers: (nutrition.fibers / servingsPerMeal).toFixed(0),
        valueSugars: (nutrition.sugars / servingsPerMeal).toFixed(0),
        valueProteins: (nutrition.proteins / servingsPerMeal).toFixed(0),
        // valueVitaminD: (
        //   ((nutrition.vitamind / 20000) * 100) /
        //   servingsPerMeal
        // ).toFixed(0),
        // valuePotassium_2018: (
        //   ((nutrition.potassium / 4700) * 100) /
        //   servingsPerMeal
        // ).toFixed(0),
        // valueCalcium: (
        //   ((nutrition.calcium / 1300) * 100) /
        //   servingsPerMeal
        // ).toFixed(0),
        // valueIron: (((nutrition.iron / 18) * 100) / servingsPerMeal).toFixed(0),
        valueAddedSugars: (nutrition.addedsugars / servingsPerMeal).toFixed(0),
        showLegacyVersion: false
      };

      return {
        showItemName: false,
        showServingUnitQuantity: true,
        valueServingPerContainer: servingsPerMeal,
        valueServingUnitQuantity: 1,
        valueServingSizeUnit: servingSizeUnit,
        showServingsPerContainer: true,
        showPolyFat: false,
        showMonoFat: false,
        showTransFat: false,
        showFibers: true,
        showVitaminD: false,
        showPotassium_2018: false,
        showCalcium: false,
        showIron: false,
        showCaffeine: false,
        itemName: meal.title,
        ingredientList: ingredientList,
        showIngredients: false,
        decimalPlacesForQuantityTextbox: 2,
        allowFDARounding: false,
        decimalPlacesForNutrition: 0,
        valueCalories: (nutrition.calories / servingsPerMeal).toFixed(0),
        valueFatCalories: (nutrition.fatcalories / servingsPerMeal).toFixed(0),
        valueTotalFat: (nutrition.totalfat / servingsPerMeal).toFixed(0),
        valueSatFat: (nutrition.satfat / servingsPerMeal).toFixed(0),
        valueTransFat: (nutrition.transfat / servingsPerMeal).toFixed(0),
        valueCholesterol: (nutrition.cholesterol / servingsPerMeal).toFixed(0),
        valueSodium: (nutrition.sodium / servingsPerMeal).toFixed(0),
        valueTotalCarb: (nutrition.totalcarb / servingsPerMeal).toFixed(0),
        valueFibers: (nutrition.fibers / servingsPerMeal).toFixed(0),
        valueSugars: (nutrition.sugars / servingsPerMeal).toFixed(0),
        valueProteins: (nutrition.proteins / servingsPerMeal).toFixed(0),
        // valueVitaminD: (
        //   ((nutrition.vitamind / 20000) * 100) /
        //   servingsPerMeal
        // ).toFixed(0),
        // valuePotassium_2018: (
        //   ((nutrition.potassium / 4700) * 100) /
        //   servingsPerMeal
        // ).toFixed(0),
        // valueCalcium: (
        //   ((nutrition.calcium / 1300) * 100) /
        //   servingsPerMeal
        // ).toFixed(0),
        // valueIron: (((nutrition.iron / 18) * 100) / servingsPerMeal).toFixed(0),
        valueAddedSugars: (nutrition.addedsugars / servingsPerMeal).toFixed(0),
        showLegacyVersion: false
      };
    },
    showBag() {
      if (this.storeView || this.$route.params.storeView) return;
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
    backToMenu(categoryTarget = null, speed = 700) {
      if (categoryTarget) {
        this.activeCatId = categoryTarget.substring(
          categoryTarget.indexOf("_") + 1
        );
      }
      this.showMealsArea = true;
      this.showMealPackagesArea = true;
      this.mealPageView = false;
      this.mealPackagePageView = false;
      this.finalCategoriesSub = [];
      if (categoryTarget) {
        this.$nextTick(() => {
          scrollToCategory(categoryTarget, speed);
        });
      }
      this.$router.push(this.$route.path);
    },
    backFromPackagePage() {
      this.$refs.mealPackagePage.back();
      this.$router.push(this.$route.path);
    },
    addFromPackagePage() {
      this.$refs.mealPackagePage.done();
    },
    showDeliveryDateModal() {
      this.showDeliveryDayModal = true;
    },
    showVariations(data) {
      this.meal = data.meal;
      this.sizeId = data.sizeId;
      this.showVariationsModal = true;
    },
    autoPickUpcomingMultDD(availableDates) {
      if (!availableDates) {
        if (this.isMultipleDelivery) {
          // let week_index = this.storeSettings.next_orderable_delivery_dates[0]
          //   .week_index;
          // let nextDeliveryDay = this.store.delivery_days.find(day => {
          //   return day.day == week_index;
          // });

          let nextDeliveryDay = this.sortedDeliveryDays[0];

          this.selectedDeliveryDay = nextDeliveryDay;
          this.finalDeliveryDay = nextDeliveryDay;

          store.dispatch("refreshLazyDD", {
            delivery_day: this.finalDeliveryDay
          });
        }
      } else {
        this.selectedDeliveryDay = availableDates[0];
        this.finalDeliveryDay = availableDates[0];

        if (availableDates.length > 0) {
          store.dispatch("refreshLazyDD", {
            delivery_day: availableDates[0]
          });
        }
      }
    },
    getBrandColor(delivery_day) {
      if (this.selectedDeliveryDay) {
        if (
          this.selectedDeliveryDay.day_friendly == delivery_day.day_friendly
        ) {
          if (this.store.settings) {
            let style = "background-color:";
            style += this.store.settings.color;
            return style;
          }
        }
      }
    },
    slugifyItem(item) {
      let title = item.title;
      title = title.replace(/\s+/g, "-").toLowerCase();
      return title;
    },
    changeTransferType(val) {
      this.$store.commit("emptyBag");
      this.setBagPickup(val);
      this.setBagZipCode(null);
      if (
        !this.bagZipCode &&
        val == 0 &&
        this.store.delivery_day_zip_codes.length > 0
      ) {
        this.setMultDDZipCode(0);
      }
      this.autoPickUpcomingMultDD(null);
    },
    async clearInactiveItems() {
      await axios.get("/api/refresh_inactive_meal_ids").then(resp => {
        this.bag.forEach(item => {
          if (resp.data.includes(item.meal.id)) {
            this.clearMealFullQuantity(
              item.meal,
              item.meal_package,
              item.size,
              item.components,
              item.addons,
              item.special_instructions
            );
          }
        });
      });
    },
    removeOldDeliveryDates() {
      this.bag.forEach(item => {
        if (item.delivery_day) {
          if (moment(item.delivery_day.day_friendly).isBefore(moment())) {
            this.clearMealFullQuantity(
              item.meal,
              item.meal_package,
              item.size,
              item.components,
              item.addons,
              item.special_instructions
            );
          }
        }
      });
    },
    goToCheckout() {
      if (!this.minimumMet) {
        return;
      }
      this.$router.push({
        name: "customer-bag",
        params: {
          subscriptionId: this.subscriptionId,
          transferTime: this.transferTime,
          staffMember: this.staffMember,
          pickup: this.pickup,
          inSub: this.inSub,
          weeklySubscriptionValue: this.weeklySubscriptionValue,
          lineItemOrders: this.lineItemOrders,
          subscription: this.subscription
        },
        query: {
          r: this.$route.query.r,
          sub: this.$route.query.sub
        }
      });
    },
    hasItems(category = null, group = null) {
      if (this.mealPackagePageView) {
        return true;
      }
      if (group) {
        category = this.mealsMix.find(cat => {
          return cat.category_id === group.id;
        });
      }

      let meals = category ? category.meals : [];

      if (this.store.modules.frequencyItems) {
        if (this.adjustMealPlan || this.$route.query.sub === "true") {
          meals = meals.filter(meal => {
            return meal.frequencyType !== "order";
          });
        }
        if (this.adjustOrder) {
          meals = meals.filter(meal => {
            return meal.frequencyType !== "sub";
          });
        }
      }
      return meals.length > 0 ? true : false;
    }
  }
};
</script>
