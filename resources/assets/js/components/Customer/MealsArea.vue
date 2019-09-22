<template>
  <div>
    <meal-variations-area
      ref="componentModal"
      :key="total"
    ></meal-variations-area>

    <div
      v-for="(group, catIndex) in meals"
      :key="group.category"
      :id="slugify(group.category)"
      v-observe-visibility="
        (isVisible, entry) => $parent.onCategoryVisible(isVisible, catIndex)
      "
      class="main-customer-container customer-menu-container left-right-box-shadow"
      v-if="$parent.showMealsArea"
    >
      <store-closed></store-closed>
      <outside-delivery-area></outside-delivery-area>
      <h2 class="text-center mb-3 dbl-underline">
        {{ group.category }}
      </h2>
      <div class="row">
        <div
          class="item col-sm-6 col-lg-4 col-xl-3 pl-1 pr-0 pl-sm-3 pr-sm-3 meal-border"
          v-for="(meal, i) in group.meals"
          :key="meal.id"
        >
          <div :class="card" @click="showMeal(meal)">
            <div :class="cardBody">
              <div class="item-wrap">
                <div class="title d-md-none">
                  {{ meal.title }}
                </div>

                <div class="image">
                  <thumbnail
                    v-if="meal.image.url_medium"
                    :src="meal.image.url_medium"
                    class="menu-item-img"
                    width="100%"
                    style="background-color:#ffffff"
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
                        @click.stop="minusOne(meal)"
                        class="plus-minus gray"
                      >
                        <i>-</i>
                      </b-btn>
                      <b-form-input
                        type="text"
                        name
                        id
                        class="quantity"
                        :value="mealQuantity(meal)"
                        readonly
                      ></b-form-input>
                      <b-btn
                        @click.stop="addMeal(meal, null)"
                        class="menu-bag-btn plus-minus"
                      >
                        <i>+</i>
                      </b-btn>
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
import StoreClosed from "../../components/Customer/StoreClosed";
import MealVariationsArea from "../../components/Modals/MealVariationsArea";

export default {
  components: {
    OutsideDeliveryArea,
    StoreClosed,
    MealVariationsArea,
    StoreClosed
  },
  props: {
    meals: "",
    card: "",
    cardBody: ""
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      storeSettings: "viewedStoreSetting",
      total: "bagQuantity",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    })
  },
  methods: {
    addMeal(meal, mealPackage) {
      if (
        meal.sizes.length > 0 ||
        meal.components.length > 0 ||
        meal.addons.length > 0
      ) {
        this.showMeal(meal);
        return;
      }
      this.addOne(meal, false, null, null, null, null);
      if (this.$parent.showBagClass.includes("hidden-right")) {
        this.$parent.showBagClass = "shopping-cart show-right bag-area";
      }
      if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass += " area-scroll";
      } else if (this.$parent.showBagScrollbar) {
        this.$parent.showBagClass -= " area-scroll";
      }
    },
    showMeal(meal) {
      this.$parent.showMealPage(meal);
      this.$parent.showMealsArea = false;
      this.$parent.showMealPackagesArea = false;
    }
  }
};
</script>
