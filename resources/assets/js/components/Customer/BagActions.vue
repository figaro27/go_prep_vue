<template>
  <div>
    <div>
      <div class="row" v-if="!bagView">
        <div class="col-md-7">
          <p class="mt-4 ml-2" v-if="!minimumMet && !storeView">
            {{ addMore }}
          </p>
        </div>
        <div class="col-md-5">
          <p class="small pl-2 pt-2">Subtotal</p>
          <h4 class="pl-2">
            {{
              format.money(totalBagPricePreFees, this.storeSettings.currency)
            }}
          </h4>
        </div>
      </div>

      <router-link
        :to="{
          name: 'customer-bag',
          params: { subscriptionId: subscriptionId }
        }"
        v-if="minimumMet && !storeView && !bagView"
      >
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>

      <router-link
        v-if="storeView"
        :to="{
          name: 'store-bag',
          params: {
            storeView: storeView,
            manualOrder: manualOrder,
            subscriptionId: subscriptionId,
            orderId: orderId,
            order: order,
            adjustOrder: adjustOrder,
            adjustMealPlan: adjustMealPlan,
            preview: preview,
            deliveryDay: deliveryDay,
            transferTime: transferTime,
            pickup: pickup,
            checkoutData: checkoutData,
            forceValue: forceValue
          }
        }"
      >
        <b-btn class="menu-bag-btn">NEXT</b-btn>
      </router-link>

      <router-link to="/customer/menu">
        <b-btn
          v-if="$route.name === 'customer-bag' && !minimumMet"
          class="menu-bag-btn mb-2"
          >BACK</b-btn
        >
      </router-link>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";
import format from "../../lib/format";

export default {
  props: {
    preview: false,
    storeView: false,
    manualOrder: false,
    adjustOrder: false,
    adjustMealPlan: false,
    subscriptionId: 0,
    orderId: null,
    order: null,
    deliveryDay: null,
    transferTime: null,
    pickup: null,
    checkoutData: null,
    forceValue: false
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      creditCards: "cards",
      store: "viewedStore",
      storeModules: "viewedStoreModules",
      storeModuleSettings: "viewedStoreModuleSettings",
      storeCustomers: "storeCustomers",
      total: "bagQuantity",
      bag: "bagItems",
      coupon: "bagCoupon",
      deliveryPlan: "bagMealPlan",
      mealPlan: "bagMealPlan",
      hasMeal: "bagHasMeal",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      willDeliver: "viewedStoreWillDeliver",
      isLoading: "isLoading",
      storeLogo: "viewedStoreLogo",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      coupons: "viewedStoreCoupons",
      pickupLocations: "viewedStorePickupLocations",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      _orders: "orders",
      loggedIn: "loggedIn"
    }),
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
        return "items";
      }
      return "item";
    },
    minimumMet() {
      if (
        (this.minOption === "meals" && this.total >= this.minMeals) ||
        (this.minOption === "price" &&
          this.totalBagPricePreFees >= this.minPrice)
      )
        return true;
      else return false;
    },
    addMore() {
      if (this.minOption === "meals")
        return (
          "Please add " +
          this.remainingMeals +
          " " +
          this.singOrPlural +
          " to continue."
        );
      else if (this.minOption === "price")
        return (
          "Please add " +
          format.money(this.remainingPrice, this.storeSettings.currency) +
          " more to continue."
        );
    },
    bagView() {
      if (
        this.$route.name === "customer-bag" ||
        this.$route.name === "store-bag"
      )
        return true;
      else return false;
    }
  }
};
</script>
