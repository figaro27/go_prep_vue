<template>
  <div>
    <ul class="list-group">
      <li v-for="(item, mealId) in bag" :key="`bag-${mealId}`" class="bag-item">
        <div v-if="item && item.quantity > 0" class="d-flex align-items-center">
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
                addOne(item.meal, true, item.size, item.components, item.addons)
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
              v-if="item.meal.image.url_thumb"
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

            <ul v-if="item.components" class="plain">
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
    <p
      class="mt-3"
      v-if="minOption === 'meals' && total < minMeals && !manualOrder"
    >
      Please add {{ remainingMeals }} {{ singOrPlural }} to continue.
    </p>
    <router-link to="/customer/menu">
      <b-btn
        v-if="
          minOption === 'meals' && total < minMeals && !preview && !manualOrder
        "
        class="menu-bag-btn mb-2"
        >BACK</b-btn
      >
    </router-link>
    <router-link to="/store/manual-order">
      <b-btn
        v-if="
          minOption === 'meals' && total < minMeals && !preview && manualOrder
        "
        class="menu-bag-btn mb-2"
        >BACK</b-btn
      >
    </router-link>

    <p
      class="mt-3"
      v-if="
        minOption === 'price' && totalBagPricePreFees < minPrice && !manualOrder
      "
    >
      Please add
      {{ format.money(remainingPrice, storeSettings.currency) }} more to
      continue.
    </p>

    <div>
      <router-link to="/customer/menu">
        <b-btn
          v-if="
            minOption === 'price' &&
              totalBagPricePreFees <= minPrice &&
              !preview &&
              !manualOrder
          "
          class="menu-bag-btn"
          >BACK</b-btn
        >
      </router-link>
      <router-link to="/store/manual-order">
        <b-btn
          v-if="
            minOption === 'price' &&
              totalBagPricePreFees <= minPrice &&
              !preview &&
              manualOrder
          "
          class="menu-bag-btn"
          >BACK</b-btn
        >
      </router-link>
    </div>
    <li
      class="transfer-instruction mt-2"
      v-if="
        transferTypeCheckDelivery &&
          pickup === 0 &&
          storeSettings.deliveryInstructions &&
          !manualOrder
      "
    >
      <p class="strong">Delivery Instructions:</p>
      <p v-html="storeSettings.deliveryInstructions"></p>
    </li>
    <li
      class="transfer-instruction mt-2"
      v-if="
        transferTypeCheckPickup &&
          pickup === 1 &&
          storeSettings.pickupInstructions &&
          !manualOrder
      "
    >
      <p class="strong">Pickup Instructions:</p>
      <p v-html="storeSettings.pickupInstructions">
        {{ storeSettings.pickupInstructions }}
      </p>
    </li>

    <div v-if="storeSettings.open === false">
      <div class="row">
        <div class="col-sm-12 mt-3">
          <div class="card">
            <div class="card-body">
              <h5 class="center-text">
                This company will not be taking new orders at this time.
              </h5>
              <p class="center-text mt-3">
                <strong>Reason:</strong>
                {{ storeSettings.closedReason }}
              </p>
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
