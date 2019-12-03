<template>
  <div
    v-if="$parent.showMealsArea"
    style="min-height: 100%;"
    v-bind:class="
      storeSettings.menuStyle === 'image'
        ? 'left-right-box-shadow main-customer-container'
        : 'left-right-box-shadow main-customer-container gray-background'
    "
  >
    <meal-package-components-modal
      ref="packageComponentModal"
      :packageTitle="packageTitle"
    ></meal-package-components-modal>

    <div
      v-for="(group, catIndex) in meals"
      :key="'category_' + group.category + '_' + catIndex"
      :id="slugify(group.category)"
      :target="'categorySection_' + group.category_id"
      :class="container"
      style="margin-bottom: 20px;"
      v-if="group.meals.length > 0 && isCategoryVisible(group)"
    >
      <div
        v-observe-visibility="
          (isVisible, entry) => $parent.onCategoryVisible(isVisible, group)
        "
      >
        <div v-if="storeSettings.menuStyle === 'image'">
          <h2 class="text-center mb-2 dbl-underline">
            {{ group.category }}
          </h2>
          <h5 v-if="group.subtitle !== null" class="text-center mb-4">
            {{ group.subtitle }}
          </h5>
          <div class="row">
            <div
              class="item col-sm-6 col-md-6 col-lg-6 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3 meal-border pb-2 mb-2"
              v-for="(meal, index) in group.meals"
              :key="
                meal.meal_package
                  ? 'meal_package_' +
                    meal.id +
                    '_' +
                    group.category_id +
                    '_' +
                    index
                  : 'meal_' + meal.id + '_' + group.category_id + '_' + index
              "
            >
              <div :class="card">
                <div :class="cardBody">
                  <div class="item-wrap">
                    <div class="title d-md-none">
                      <strong>{{ meal.title }}</strong>
                    </div>

                    <div class="image">
                      <thumbnail
                        v-if="meal.image != null && meal.image.url_medium"
                        :src="meal.image.url_medium"
                        class="menu-item-img"
                        width="100%"
                        style="background-color:#ffffff"
                        @click="showMeal(meal, group)"
                      ></thumbnail>

                      <div class="price">
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </div>
                    </div>

                    <div class="meta">
                      <div class="title d-none d-md-block center-text">
                        <strong>{{ meal.title }}</strong>
                      </div>
                      <div
                        class="title"
                        v-if="meal.macros && storeSettings.showMacros"
                      >
                        <div class="row">
                          <div class="col-12 col-md-3">
                            <div class="row">
                              <p class="small strong col-6 col-md-12">
                                Calories
                              </p>
                              <p class="small col-6 col-md-12">
                                {{ meal.macros.calories }}
                              </p>
                            </div>
                          </div>
                          <div class="col-12 col-md-3">
                            <div class="row">
                              <p class="small strong col-6 col-md-12">
                                Carbs
                              </p>
                              <p class="small col-6 col-md-12">
                                {{ meal.macros.carbs }}
                              </p>
                            </div>
                          </div>
                          <div class="col-12 col-md-3">
                            <div class="row">
                              <p class="small strong col-6 col-md-12">
                                Protein
                              </p>
                              <p class="small col-6 col-md-12">
                                {{ meal.macros.protein }}
                              </p>
                            </div>
                          </div>
                          <div class="col-12 col-md-3">
                            <div class="row">
                              <p class="small strong col-6 col-md-12">
                                Fat
                              </p>
                              <p class="small col-6 col-md-12">
                                {{ meal.macros.fat }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="description d-md-none">
                        {{ meal.description }}
                      </div>

                      <div class="actions">
                        <div
                          class="d-flex justify-content-between align-items-center mt-1"
                        >
                          <b-btn
                            @click.stop="minusMixOne(meal)"
                            class="plus-minus gray"
                            v-if="meal.gift_card"
                          >
                            <i>-</i>
                          </b-btn>
                          <b-form-input
                            v-if="meal.gift_card"
                            type="text"
                            name
                            id
                            class="quantity"
                            :value="mealMixQuantity(meal)"
                            readonly
                          ></b-form-input>

                          <b-btn
                            v-if="meal.gift_card"
                            @click.stop="addMeal(meal, null)"
                            class="menu-bag-btn plus-minus"
                          >
                            <i>+</i>
                          </b-btn>

                          <b-btn
                            @click.stop="minusMixOne(meal)"
                            class="plus-minus gray"
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                meal.sizes.length === 0
                            "
                          >
                            <i>-</i>
                          </b-btn>
                          <b-form-input
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                meal.sizes.length === 0
                            "
                            type="text"
                            name
                            id
                            class="quantity"
                            :value="mealMixQuantity(meal)"
                            readonly
                          ></b-form-input>

                          <b-btn
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                (!meal.sizes || meal.sizes.length === 0)
                            "
                            @click.stop="addMeal(meal, null)"
                            class="menu-bag-btn plus-minus"
                          >
                            <i>+</i>
                          </b-btn>

                          <b-dropdown
                            right
                            v-if="
                              !meal.meal_package &&
                                !meal.gift_card &&
                                meal.sizes &&
                                meal.sizes.length > 0
                            "
                            toggle-class="brand-color"
                            :ref="
                              'dropdown_' + meal.id + '_' + group.category_id
                            "
                            class="mx-auto"
                            size="lg"
                          >
                            <span class="white-text" slot="button-content"
                              >Select</span
                            >
                            <b-dropdown-item
                              @click="addMeal(meal, false)"
                              class="variation-dropdown"
                            >
                              {{ meal.default_size_title || "Regular" }} -
                              {{
                                format.money(meal.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                            <b-dropdown-item
                              class="variation-dropdown"
                              v-for="(size, index) in meal.sizes"
                              :key="'size_' + size.id + '_' + index"
                              @click.stop="addMeal(meal, false, size)"
                            >
                              {{ size.title }} -
                              {{
                                format.money(size.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                          </b-dropdown>

                          <!-- <b-btn
                            v-if="
                              meal.meal_package &&
                                (!meal.sizes || meal.sizes.length === 0)
                            "
                            @click="addMeal(meal, false)"
                            class="plus-minus menu-bag-btn"
                          >
                            <i>+</i>
                          </b-btn> -->

                          <b-btn
                            slot="button-content"
                            class="brand-color mx-auto white-text"
                            size="lg"
                            v-if="
                              meal.meal_package &&
                                !meal.gift_card &&
                                (!meal.sizes || meal.sizes.length === 0)
                            "
                            @click="addMeal(meal, true)"
                            >Select</b-btn
                          >

                          <b-dropdown
                            v-if="
                              meal.meal_package &&
                                meal.sizes &&
                                meal.sizes.length > 0
                            "
                            toggle-class="brand-color"
                            :ref="
                              'dropdown_' + meal.id + '_' + group.category_id
                            "
                            class="mx-auto"
                            size="lg"
                            right
                          >
                            <span class="white-text" slot="button-content"
                              >Select</span
                            >

                            <b-dropdown-item
                              @click="addMeal(meal, true)"
                              class="variation-dropdown"
                            >
                              {{ meal.default_size_title || "Regular" }} -
                              {{
                                format.money(meal.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                            <b-dropdown-item
                              class="variation-dropdown"
                              v-for="(size, index) in meal.sizes"
                              :key="'size_' + size.id + '_' + index"
                              @click="addMealPackage(meal, true, size)"
                            >
                              {{ size.title }} -
                              {{
                                format.money(size.price, storeSettings.currency)
                              }}
                            </b-dropdown-item>
                          </b-dropdown>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="storeSettings.menuStyle === 'text'">
          <h2 class="text-center mb-3 dbl-underline">
            {{ group.category }}
          </h2>
          <div class="row">
            <div
              class="item item-text col-sm-6 col-md-6 col-lg-12 col-xl-6"
              v-for="(meal, index) in group.meals"
              :key="'meal_' + meal.id + '_' + index"
              style="margin-bottom: 10px !important;"
            >
              <div
                class="card card-text-menu border-light p-3 mr-1"
                @click="showMeal(meal, group)"
                style="height: 100%;"
              >
                <!--<div class="bag-item-quantity row">!-->
                <div
                  class="bag-item-quantity"
                  style="display: flex; min-height: 128px !important;"
                >
                  <!--<div class="col-md-1">!-->
                  <div class="button-area" style="position: relative;">
                    <!-- <div
                      @click.stop="addMeal(item, null)"
                      class="bag-plus-minus small-buttons brand-color white-text"
                    >
                      <i>+</i>
                    </div> -->

                    <b-btn
                      v-if="
                        !meal.meal_package &&
                          !meal.gift_card &&
                          (!meal.sizes || meal.sizes.length === 0)
                      "
                      @click.stop="addMeal(meal, null)"
                      class="menu-bag-btn small-buttons plus-minus"
                    >
                      <i>+</i>
                    </b-btn>

                    <b-dropdown
                      v-if="
                        !meal.meal_package &&
                          !meal.gift_card &&
                          meal.sizes &&
                          meal.sizes.length > 0
                      "
                      toggle-class="menu-bag-btn small-buttons plus-minus"
                      :ref="'dropdown_' + meal.id + '_' + group.category_id"
                    >
                      <i
                        slot="button-content"
                        :id="'dropdown_' + meal.id + '_' + group.category_id"
                        >+</i
                      >
                      <b-dropdown-item
                        @click.stop="addMeal(meal, false)"
                        class="variation-dropdown"
                      >
                        {{ meal.default_size_title || "Regular" }} -
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </b-dropdown-item>
                      <b-dropdown-item
                        class="variation-dropdown"
                        v-for="(size, index) in meal.sizes"
                        :key="'size_' + size.id + '_' + index"
                        @click.stop="addMeal(meal, false, size)"
                      >
                        {{ size.title }} -
                        {{ format.money(size.price, storeSettings.currency) }}
                      </b-dropdown-item>
                    </b-dropdown>

                    <b-btn
                      v-if="
                        meal.meal_package &&
                          !meal.gift_card &&
                          (!meal.sizes || meal.sizes.length === 0)
                      "
                      @click.stop="addMeal(meal, false)"
                      class="menu-bag-btn small-buttons plus-minus"
                    >
                      <i>+</i>
                    </b-btn>

                    <b-dropdown
                      v-if="
                        meal.meal_package && meal.sizes && meal.sizes.length > 0
                      "
                      toggle-class="menu-bag-btn small-buttons plus-minus"
                      :ref="'dropdown_' + meal.id + '_' + group.category_id"
                    >
                      <i
                        slot="button-content"
                        :id="'dropdown_' + meal.id + '_' + group.category_id"
                        >+</i
                      >
                      <b-dropdown-item
                        @click="addMeal(meal, true)"
                        class="variation-dropdown"
                      >
                        {{ meal.default_size_title || "Regular" }} -
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </b-dropdown-item>
                      <b-dropdown-item
                        class="variation-dropdown"
                        v-for="(size, index) in meal.sizes"
                        :key="'size_' + size.id + '_' + index"
                        @click="addMealPackage(meal, true, size)"
                      >
                        {{ size.title }} -
                        {{ format.money(size.price, storeSettings.currency) }}
                      </b-dropdown-item>
                    </b-dropdown>

                    <p class="mt-3 ml-1">{{ mealMixQuantity(meal) }}</p>
                    <!-- <b-form-input
                      type="text"
                      name
                      id
                      class="quantity small-quantity"
                      style="text-align: center; padding: 0;"
                      :value="mealMixQuantity(meal)"
                      readonly
                    ></b-form-input> -->
                    <div
                      @click.stop="minusMixOne(meal)"
                      class="bag-plus-minus small-buttons gray white-text"
                    >
                      <i>-</i>
                    </div>
                  </div>

                  <!--<div v-if="meal.image != null" class="col-md-8">!-->
                  <div
                    v-if="meal.image != null"
                    class="content-area"
                    style="position: relative;"
                  >
                    <div class="image-area" style="position: relative;">
                      <thumbnail
                        class="text-menu-image"
                        v-if="meal.image != null"
                        :src="meal.image.url_thumb"
                        :spinner="false"
                      ></thumbnail>
                      <div
                        class="price"
                        style="top: 5px !important; right: 5px !important;"
                      >
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </div>
                    </div>

                    <div class="content-text-wrap">
                      <strong style="word-break: break-all;">{{
                        meal.title
                      }}</strong>
                      <div class="mt-1 content-text">
                        {{ meal.description }}
                      </div>
                    </div>
                  </div>
                  <div v-else class="content-area" style="position: relative;">
                    <div class="content-text-wrap">
                      <!--<div v-else class="col-md-11">!-->
                      <strong style="word-break: break-all;">{{
                        meal.title
                      }}</strong>
                      <div class="mt-1 content-text">
                        {{ meal.description }}
                      </div>
                      <div
                        class="price-no-bg"
                        style="top: 0 !important; right: 0 !important;"
                      >
                        {{ format.money(meal.price, storeSettings.currency) }}
                      </div>
                    </div>
                  </div>

                  <!--<div v-if="meal.image != null" class="col-md-3">!-->
                  <!--<div
                    v-if="meal.image != null"
                    class="image-area"
                    style="position: relative; width: 128px;"
                  >
                    <thumbnail
                      class="text-menu-image"
                      v-if="meal.image != null"
                      :src="meal.image.url_thumb"
                      :spinner="false"
                    ></thumbnail>
                    <div
                      class="price"
                      style="top: 5px !important; right: 5px !important;"
                    >
                      {{ format.money(meal.price, storeSettings.currency) }}
                    </div>
                  </div>!-->
                </div>
                <div
                  class="title"
                  v-if="meal.macros && storeSettings.showMacros"
                >
                  <div class="row">
                    <div class="col-12 col-md-3">
                      <div class="row">
                        <p class="small strong col-6 col-md-12">
                          Calories
                        </p>
                        <p class="small col-6 col-md-12">
                          {{ meal.macros.calories }}
                        </p>
                      </div>
                    </div>
                    <div class="col-12 col-md-3">
                      <div class="row">
                        <p class="small strong col-6 col-md-12">
                          Carbs
                        </p>
                        <p class="small col-6 col-md-12">
                          {{ meal.macros.carbs }}
                        </p>
                      </div>
                    </div>
                    <div class="col-12 col-md-3">
                      <div class="row">
                        <p class="small strong col-6 col-md-12">
                          Protein
                        </p>
                        <p class="small col-6 col-md-12">
                          {{ meal.macros.protein }}
                        </p>
                      </div>
                    </div>
                    <div class="col-12 col-md-3">
                      <div class="row">
                        <p class="small strong col-6 col-md-12">
                          Fat
                        </p>
                        <p class="small col-6 col-md-12">
                          {{ meal.macros.fat }}
                        </p>
                      </div>
                    </div>
                  </div>
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
import MenuBag from "../../mixins/menuBag";
import { mapGetters } from "vuex";
import OutsideDeliveryArea from "../../components/Customer/OutsideDeliveryArea";
import MealVariationsArea from "../../components/Modals/MealVariationsArea";
import MealPackageComponentsModal from "../../components/Modals/MealPackageComponentsModal";
import store from "../../store";

export default {
  data() {
    return {
      packageTitle: null
    };
  },
  components: {
    MealVariationsArea,
    MealPackageComponentsModal
  },
  props: {
    meals: "",
    card: "",
    cardBody: "",
    filters: null,
    search: "",
    filteredView: false,
    adjustOrder: false
  },
  mounted: function() {},
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      context: "context",
      isLazy: "isLazy",
      //total: "bagQuantity",
      //hasMeal: "bagHasMeal",
      //minOption: "minimumOption",
      //minMeals: "minimumMeals",
      //minPrice: "minimumPrice",
      bag: "bagItems",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      _categories: "viewedStoreCategories"
    }),
    isMultipleDelivery() {
      return this.store.modules.multipleDeliveryDays == 1 ? true : false;
    },
    storeSettings() {
      return this.store.settings;
    },
    container() {
      if (this.storeSettings.menuStyle === "image") {
        return "categorySection customer-menu-container";
      } else {
        return "categorySection customer-menu-container";
      }
    }
  },
  methods: {
    existInBagItem(meal, meal_size, item) {
      const mealPackage = !!item.meal_package;

      if (!mealPackage || !item.meal) {
        return false;
      }

      const meal_size_id = meal_size ? meal_size.id : null;

      let found = false;
      const pkg = this.getMealPackage(item.meal.id, item.meal);
      const size = pkg && item.size ? item.size : null;
      const packageMeals = size ? size.meals : pkg ? pkg.meals : null;

      if (packageMeals) {
        packageMeals.forEach(pkgMeal => {
          if (
            pkgMeal &&
            meal.id == pkgMeal.id &&
            meal_size_id == pkgMeal.meal_size_id &&
            !found
          ) {
            found = true;
          }
        });
      }

      if (!found) {
        _(item.components).forEach((options, componentId) => {
          const component = pkg.getComponent(componentId);
          const optionIds = mealPackage ? Object.keys(options) : options;

          _.forEach(optionIds, optionId => {
            const option = pkg.getComponentOption(component, optionId);
            if (!option) {
              return null;
            }

            if (option.selectable) {
              _.forEach(options[option.id], optionItem => {
                if (
                  optionItem &&
                  optionItem.meal_id == meal.id &&
                  optionItem.meal_size_id == meal_size_id &&
                  !found
                ) {
                  found = true;
                }
              });
            } else {
              _.forEach(option.meals, mealItem => {
                if (
                  mealItem &&
                  mealItem.meal_id == meal.id &&
                  mealItem.meal_size_id == meal_size_id &&
                  !found
                ) {
                  found = true;
                }
              });
            }
          });
        });
      }

      if (!found) {
        _(item.addons).forEach((addonItems, addonId) => {
          const addon = pkg.getAddon(addonId);

          if (addon.selectable) {
            _.forEach(addonItems, addonItem => {
              if (
                addonItem &&
                addonItem.meal_id == meal.id &&
                addonItem.meal_size_id == meal_size_id &&
                !found
              ) {
                found = true;
              }
            });
          } else {
            _.forEach(addonItems, addonItem => {
              if (
                addonItem &&
                addonItem.meal_id == meal.id &&
                addonItem.meal_size_id == meal_size_id &&
                !found
              ) {
                found = true;
              }
            });
          }
        });
      }

      return found;
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
    getRelatedBagItems(meal, size) {
      const items = [];
      const bag = this.bag;

      if (bag) {
        bag.forEach(item => {
          if (this.existInBagItem(meal, size, item)) {
            items.push(item);
          }
        });
      }
      return items;
    },
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
    mealMixQuantity(meal) {
      if (meal.meal_package) {
        return this.quantity(meal, true);
      } else {
        return this.mealQuantity(meal);
      }
    },
    async minusMixOne(
      meal,
      condition,
      size,
      components,
      addons,
      special_instructions
    ) {
      if (meal.meal_package) {
        this.minusOne(meal, true);
      } else {
        if (
          (meal.sizes && meal.sizes.length > 0) ||
          (meal.components && meal.components.length > 0) ||
          (meal.addons && meal.addons.length > 0)
        ) {
          this.$toastr.e("Please remove the meal from the bag.");
        } else {
          this.minusOne(meal, false, null, null, [], null);
        }
      }
    },
    async addMealPackage(mealPackage, condition = false, size) {
      if (size === undefined) {
        size = null;
      }

      if (size) {
        this.packageTitle = mealPackage.title + " - " + size.title;
      } else {
        if (mealPackage.default_size_title) {
          this.packageTitle =
            mealPackage.title + " - " + mealPackage.default_size_title;
        } else {
          this.packageTitle = mealPackage.title;
        }
      }

      /* Refresh Meal Package */
      if (!this.store.refreshed_package_ids.includes(mealPackage.id)) {
        this.$parent.forceShow = true;
        mealPackage = await store.dispatch(
          "refreshStoreMealPackage",
          mealPackage
        );
        this.$parent.forceShow = false;
      } else {
        mealPackage = this.getMealPackage(mealPackage.id);
      }
      /* Refresh Meal Package End */

      /* Show Detail Page or not */
      let showDetail = false;
      let sizeId = size ? size.id : null;
      let sizeCriteria = { meal_package_size_id: sizeId };

      if (mealPackage.sizes && mealPackage.sizes.length && sizeId) {
        size = _.find(mealPackage.sizes, { id: sizeId });
      }

      if (
        mealPackage.components &&
        mealPackage.components.length &&
        _.maxBy(mealPackage.components, "minimum") &&
        _.find(mealPackage.components, component => {
          return _.find(component.options, sizeCriteria);
        })
      ) {
        showDetail = true;
      }

      if (
        mealPackage.addons &&
        mealPackage.addons.length &&
        _.find(mealPackage.addons, sizeCriteria)
      ) {
        showDetail = true;
      }

      if (showDetail) {
        this.showMealPackage(mealPackage, size);
        return false;
      }
      /* Show Detail Page or not end */

      this.addOne(mealPackage, true, size);

      this.$parent.mealPackageModal = false;
      if (this.$parent.showBagClass.includes("hidden-right")) {
        this.$parent.showBagClass = "shopping-cart show-right bag-area";
      }
      if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass += " area-scroll";
      } else if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass -= " area-scroll";
      }
      this.$parent.search = "";
    },
    async addMeal(meal, mealPackage, size) {
      if (meal.gift_card) {
        this.addOne(meal);
        this.$parent.showBagClass = "shopping-cart show-right bag-area";
      }
      if (meal.meal_package) {
        this.addMealPackage(meal, true);
      } else {
        if (
          meal.sizes & (meal.sizes.length > 0) &&
          meal.components &&
          meal.components.length === 0 &&
          meal.addons &&
          meal.addons.length === 0
        ) {
          if (this.isAdjustOrder() || this.isManualOrder()) {
            //const items = this.getRelatedBagItems(meal, null);
            const items = this.getPackageBagItems();

            if (items && items.length > 0) {
              this.$parent.showAdjustModal(meal, null, null, [], null, items);
              return;
            } else {
              this.addOne(meal, false, null, null, [], null);
            }
          } else {
            this.addOne(meal, false, null, null, [], null);
          }
        }

        if (
          (meal.components && meal.components.length > 0) ||
          (meal.addons && meal.addons.length > 0)
        ) {
          this.showMeal(meal);
          return;
        } else {
          if (size === undefined) {
            size = null;
          }

          if (this.isAdjustOrder() || this.isManualOrder()) {
            //const items = this.getRelatedBagItems(meal, size);
            const items = this.getPackageBagItems();

            if (items && items.length > 0) {
              this.$parent.showAdjustModal(meal, size, null, [], null, items);
              return;
            } else {
              this.addOne(meal, false, size, null, [], null);
            }
          } else {
            this.addOne(meal, false, size, null, [], null);
          }
        }

        if (this.$parent.showBagClass.includes("hidden-right")) {
          this.$parent.showBagClass = "shopping-cart show-right bag-area";
        }
        if (this.$parent.showBagScrollbar) {
          this.$parent.showBagClass += " area-scroll";
        } else if (this.$parent.showBagScrollbar) {
          this.$parent.showBagClass -= " area-scroll";
        }
      }
      this.$parent.search = "";
    },
    showMealPackage(mealPackage, size) {
      $([document.documentElement, document.body]).scrollTop(0);
      this.$parent.showMealPackagePage(mealPackage, size);
      this.$parent.showMealsArea = false;
      this.$parent.showMealPackagesArea = false;
      this.$parent.search = "";
    },
    showMeal(meal, group) {
      if (meal.meal_package) {
        if (!meal.sizes || meal.sizes.length === 0) {
          this.addMealPackage(meal, true);
        } else {
          $("#dropdown_" + meal.id + "_" + group.category_id).click();
        }
      } else {
        $([document.documentElement, document.body]).scrollTop(0);
        this.$parent.showMealPage(meal);
        this.$parent.showMealsArea = false;
        this.$parent.showMealPackagesArea = false;
      }
      this.$parent.search = "";
    }
  }
};
</script>
