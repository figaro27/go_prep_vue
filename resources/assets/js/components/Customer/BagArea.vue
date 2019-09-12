<template>
  <div>
    <div class="bag-header center-text pt-3">
      <h3 class="d-inline ml-3 float-left">
        <i class="fa fa-angle-right white-text" @click="$parent.showBag()"></i>
      </h3>
      <h3 class="white-text d-inline">
        My Bag
      </h3>
      <p class="white-text d-inline">({{ total }} Meals)</p>
    </div>
    <div class="shopping-cart-meals">
      <ul class="list-group">
        <li
          v-for="(item, mealId) in bag"
          :key="`bag-${mealId}`"
          class="bag-item"
        >
          <div
            v-if="item && item.quantity > 0"
            class="d-flex align-items-center"
          >
            <div class="bag-item-quantity mr-2">
              <div
                v-if="!item.meal_package"
                @click="
                  addOne(
                    item.meal,
                    false,
                    item.size,
                    item.components,
                    item.addons
                  )
                "
                class="bag-plus-minus brand-color white-text"
              >
                <i>+</i>
              </div>
              <div
                v-if="item.meal_package"
                @click="
                  addOne(
                    item.meal,
                    true,
                    item.size,
                    item.components,
                    item.addons
                  )
                "
                class="bag-plus-minus brand-color white-text"
              >
                <i>+</i>
              </div>
              <p class="bag-quantity">{{ item.quantity }}</p>
              <div
                @click="
                  minusOne(
                    item.meal,
                    false,
                    item.size,
                    item.components,
                    item.addons
                  )
                "
                class="bag-plus-minus gray white-text"
              >
                <i>-</i>
              </div>
            </div>
            <div class="bag-item-image mr-2">
              <thumbnail
                :src="item.meal.image.url_thumb"
                :spinner="false"
                class="cart-item-img"
                width="80px"
              ></thumbnail>
            </div>
            <div class="flex-grow-1 mr-2">
              <span v-if="item.meal_package">{{ item.meal.title }}</span>
              <span v-else-if="item.size">
                {{ item.size.full_title }}
              </span>
              <span v-else>{{ item.meal.item_title }}</span>

              <ul v-if="item.components || item.addons" class="plain">
                <li v-for="component in itemComponents(item)" class="plain">
                  {{ component }}
                </li>
                <li v-for="addon in itemAddons(item)" class="plus">
                  {{ addon }}
                </li>
              </ul>
            </div>
            <div class="flex-grow-0">
              <img
                src="/images/customer/x.png"
                @click="
                  clearMeal(
                    item.meal,
                    false,
                    item.size,
                    item.components,
                    item.addons
                  )
                "
                class="clear-meal"
              />
            </div>
          </div>
        </li>
      </ul>
    </div>
    <v-style>
      .bag-header{ height:70px !important; background-color:
      {{ store.settings.color }}; margin-bottom: 15px; padding-top: 10px }
    </v-style>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";

export default {
  props: {
    manualOrder: false,
    adjustOrder: false,
    adjustMealPlan: false,
    subscriptionId: null,
    pickup: 0
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCustomers: "storeCustomers",
      storeSettings: "viewedStoreSetting",
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
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    },
    transferType() {
      return this.storeSettings.transferType.split(",");
    },
    transferTypeCheckDelivery() {
      if (_.includes(this.transferType, "delivery")) return true;
    },
    transferTypeCheckPickup() {
      if (_.includes(this.transferType, "pickup")) return true;
    },
    storeSettings() {
      return this.store.settings;
    }
  }
};
</script>
