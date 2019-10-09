<template>
  <div v-if="$parent.showMealsArea">
    <meal-variations-area ref="componentModal"></meal-variations-area>

    <meal-package-components-modal
      ref="packageComponentModal"
      :packageTitle="packageTitle"
    ></meal-package-components-modal>

    <div
      v-for="(group, catIndex) in meals"
      :key="'category_' + group.category"
      :id="slugify(group.category)"
      :target="'categorySection_' + group.category_id"
      v-observe-visibility="
        (isVisible, entry) => $parent.onCategoryVisible(isVisible, catIndex)
      "
      :class="container"
    >
      <div v-if="storeSettings.menuStyle === 'image'">
        <h2 class="text-center mb-3 dbl-underline">
          {{ group.category }}
        </h2>
        <div class="row">
          <div
            class="item col-sm-6 col-lg-4 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3 meal-border pb-2 mb-2"
            v-for="meal in group.meals"
            :key="
              meal.meal_package
                ? 'meal_package_' + meal.id + '_' + group.category_id
                : 'meal_' + meal.id + '_' + group.category_id
            "
          >
            <div :class="card">
              <div :class="cardBody">
                <div class="item-wrap">
                  <div class="title d-md-none">
                    {{ meal.title }}
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
                    <div class="title d-none d-md-block">
                      {{ meal.title }}
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
                        >
                          <i>-</i>
                        </b-btn>
                        <b-form-input
                          type="text"
                          name
                          id
                          class="quantity"
                          :value="mealMixQuantity(meal)"
                          readonly
                        ></b-form-input>

                        <b-btn
                          v-if="!meal.meal_package"
                          @click.stop="addMeal(meal, null)"
                          class="menu-bag-btn plus-minus"
                        >
                          <i>+</i>
                        </b-btn>

                        <b-btn
                          v-if="meal.meal_package && meal.sizes.length === 0"
                          @click="addMeal(meal, true)"
                          class="plus-minus menu-bag-btn"
                        >
                          <i>+</i>
                        </b-btn>

                        <b-dropdown
                          v-if="meal.meal_package && meal.sizes.length > 0"
                          toggle-class="menu-bag-btn"
                          :ref="'dropdown_' + meal.id + '_' + group.category_id"
                        >
                          <span
                            slot="button-content"
                            :id="
                              'dropdown_' + meal.id + '_' + group.category_id
                            "
                            >+</span
                          >
                          <b-dropdown-item @click="addMeal(meal, true)">
                            {{ meal.default_size_title }} -
                            {{
                              format.money(meal.price, storeSettings.currency)
                            }}
                          </b-dropdown-item>
                          <b-dropdown-item
                            v-for="size in meal.sizes"
                            :key="size.id"
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
            class="item item-text col-sm-6 col-lg-6 col-xl-6"
            v-for="item in group.meals"
            :key="item.id"
          >
            <div
              class="card card-text-menu border-light p-3 thumbnail-height mr-1"
              @click="showMeal(item, group)"
            >
              <div class="bag-item-quantity row">
                <div class="col-md-1">
                  <div
                    @click.stop="addMeal(item, null)"
                    class="bag-plus-minus small-buttons brand-color white-text"
                  >
                    <i>+</i>
                  </div>
                  <p class="mt-3 ml-1">{{ mealMixQuantity(item) }}</p>
                  <!-- <b-form-input
                    type="text"
                    name
                    id
                    class="quantity small-quantity"
                    style="text-align: center; padding: 0;"
                    :value="mealMixQuantity(item)"
                    readonly
                  ></b-form-input> -->
                  <div
                    @click.stop="
                      minusMixOne(
                        item.meal,
                        false,
                        item.size,
                        item.components,
                        item.addons,
                        item.special_instructions
                      )
                    "
                    class="bag-plus-minus small-buttons gray white-text"
                  >
                    <i>-</i>
                  </div>
                </div>
                <div v-if="item.image != null" class="col-md-8">
                  <strong>{{ item.title }}</strong>
                  <p class="mt-1">{{ item.description }}</p>
                </div>
                <div v-else class="col-md-11">
                  <strong>{{ item.title }}</strong>
                  <p class="mt-1">{{ item.description }}</p>
                  <div class="price-no-bg">
                    {{ format.money(item.price, storeSettings.currency) }}
                  </div>
                </div>

                <div v-if="item.image != null" class="col-md-3">
                  <thumbnail
                    class="text-menu-image"
                    v-if="item.image != null"
                    :src="item.image.url_thumb"
                    :spinner="false"
                  ></thumbnail>
                  <div class="price">
                    {{ format.money(item.price, storeSettings.currency) }}
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
    resetMeal: false
  },
  mounted: function() {},
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      total: "bagQuantity",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    storeSettings() {
      return this.store.settings;
    },
    container() {
      if (this.storeSettings.menuStyle === "image") {
        return "categorySection main-customer-container customer-menu-container left-right-box-shadow";
      } else {
        return "categorySection main-customer-container customer-menu-container left-right-box-shadow gray-background";
      }
    }
  },
  methods: {
    mealMixQuantity(meal) {
      if (meal.meal_package) {
        return this.quantity(meal, true);
      } else {
        return this.mealQuantity(meal);
      }
    },
    minusMixOne(
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
        this.minusOne(
          meal,
          condition,
          size,
          components,
          addons,
          special_instructions
        );
      }
    },
    addMealPackage(mealPackage, condition = false, size) {
      if (size) this.packageTitle = mealPackage.title + " - " + size.title;
      else
        this.packageTitle =
          mealPackage.title + " - " + mealPackage.default_size_title;

      this.addOne(mealPackage, condition, size);
      this.$parent.mealPackageModal = false;
      if (this.$parent.showBagClass.includes("hidden-right")) {
        this.$parent.showBagClass = "shopping-cart show-right bag-area";
      }
      if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass += " area-scroll";
      } else if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass -= " area-scroll";
      }
    },
    addMeal(meal, mealPackage) {
      if (meal.meal_package) {
        this.addMealPackage(meal, true);
      } else {
        if (
          (meal.sizes.length > 0 && !this.resetMeal) ||
          meal.components.length > 0 ||
          meal.addons.length > 0
        ) {
          this.showMeal(meal);
          return;
        }
        this.addOne(meal, false, null, null, [], null);
        if (this.$parent.showBagClass.includes("hidden-right")) {
          this.$parent.showBagClass = "shopping-cart show-right bag-area";
        }
        if (this.$parent.showBagScrollbar) {
          this.$parent.showBagClass += " area-scroll";
        } else if (this.$parent.showBagScrollbar) {
          this.$parent.showBagClass -= " area-scroll";
        }
      }
    },
    showMeal(meal, group) {
      if (meal.meal_package) {
        if (meal.sizes.length === 0) {
          this.addMealPackage(meal, true);
        } else {
          $("#dropdown_" + meal.id + "_" + group.category_id).click();
        }
      } else {
        this.$parent.showMealPage(meal);
        this.$parent.showMealsArea = false;
        this.$parent.showMealPackagesArea = false;
      }
    }
  }
};
</script>
