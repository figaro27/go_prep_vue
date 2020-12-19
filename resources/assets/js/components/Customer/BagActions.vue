<template>
  <div>
    <div>
      <div class="row" v-if="!bagView">
        <!-- <div class="col-md-7">
          <p
            class="mt-2 ml-2 strong font-16"
            style="margin-bottom:0;"
            v-if="!minimumMet && !storeView"
          >
            {{ addMore }}
          </p>
        </div> -->
        <div class="col-md-5 offset-7">
          <span class="small pl-2 pt-2">Subtotal</span>
          <h4 class="pl-2">
            {{
              format.money(
                totalBagPricePreFeesBothTypes,
                this.storeSettings.currency
              )
            }}
          </h4>
        </div>
      </div>

      <b-alert
        variant="warning"
        show
        v-if="!minimumMet && context !== 'store' && !bagView"
        class="mb-0 mt-0 pb-0 pt-2"
      >
        <p class="strong center-text font-13">{{ addMore }}</p>
      </b-alert>

      <b-btn
        @click="checkMinimum"
        v-if="!minimumMet && !storeView && !bagView"
        class="menu-bag-btn gray"
        >NEXT</b-btn
      >

      <b-btn
        :to="{
          name: 'customer-bag',
          params: {
            subscriptionId: subscriptionId,
            transferTime: transferTime,
            staffMember: staffMember,
            pickup: pickup,
            inSub: inSub,
            weeklySubscriptionValue: weeklySubscriptionValue,
            lineItemOrders: lineItemOrders,
            subscription: subscription
          },
          query: {
            r: $route.query.r,
            sub: $route.query.sub
          }
        }"
        v-if="minimumMet && !storeView && !bagView"
        class="menu-bag-btn"
        >NEXT</b-btn
      >

      <b-btn
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
            staffMember: staffMember,
            pickup: pickup,
            checkoutData: checkoutData,
            forceValue: forceValue,
            inSub: inSub,
            weeklySubscriptionValue: weeklySubscriptionValue,
            lineItemOrders: lineItemOrders,
            subscription: subscription,
            staffMember: staffMember
          }
        }"
        class="menu-bag-btn bottom-margin"
        >NEXT</b-btn
      >

      <router-link to="/customer/menu" query="$route.query">
        <b-btn
          v-if="
            $route.name === 'customer-bag' && !minimumMet && subId === undefined
          "
          class="menu-bag-btn mb-2"
          >BACK</b-btn
        >
      </router-link>
      <router-link
        :to="{
          path: '/customer/subscriptions/' + subId,
          query: { sub: this.$route.query.sub }
        }"
      >
        <b-btn
          v-if="$route.name === 'customer-bag' && !minimumMet && subId"
          class="menu-bag-btn mb-2"
          :to="{
            name: 'customer-menu',
            query: {
              r: $route.query.r
            }
          }"
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
import store from "../../store";

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
    staffMember: null,
    pickup: null,
    checkoutData: null,
    forceValue: false,
    inSub: null,
    weeklySubscriptionValue: null
  },
  data() {
    return {};
  },
  mixins: [MenuBag],
  methods: {
    addDeliveryDay() {
      this.$parent.showDeliveryDayModal = true;
    }
  },
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
      totalBagPricePreFeesBothTypes: "totalBagPricePreFeesBothTypes",
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
      loggedIn: "loggedIn",
      mealMixItems: "mealMixItems",
      context: "context"
    }),
    lineItemOrders() {
      return this.$route.params.checkoutData
        ? this.$route.params.checkoutData.lineItemOrders
        : null;
    },
    subId() {
      return this.$route.params.subscriptionId
        ? this.$route.params.subscriptionId
        : this.$route.query.subscriptionId;
    },
    subscription() {
      return this.$parent.subscription;
    },
    brandColor() {
      let style = "background-color:";
      style += this.store.settings.color;
      return style;
    },
    storeSettings() {
      return this.store.settings;
    },
    bagView() {
      if (
        this.$route.name === "customer-bag" ||
        this.$route.name === "store-bag"
      )
        return true;
      else return false;
    },
    groupBag() {
      let grouped = [];
      let groupedDD = [];

      if (this.bag) {
        if (this.isMultipleDelivery) {
          this.bag.forEach((bagItem, index) => {
            if (bagItem.delivery_day) {
              const key = "dd_" + bagItem.delivery_day.day_friendly;
              if (!groupedDD[key]) {
                groupedDD[key] = {
                  items: [],
                  delivery_day: bagItem.delivery_day
                };
              }

              groupedDD[key].items.push(bagItem);
            }
          });

          if (JSON.stringify(groupedDD) != "{}") {
            for (let i in groupedDD) {
              grouped.push(groupedDD[i]);
            }
          }

          // Add all delivery days
          if (this.selectedDeliveryDay) {
            let included = false;
            grouped.forEach(group => {
              if (group.delivery_day.id === this.selectedDeliveryDay.id) {
                included = true;
              }
            });
            if (!included) {
              grouped.push({
                items: [],
                delivery_day: this.selectedDeliveryDay
              });
            }
          }
        } else {
          grouped.push({
            items: this.bag
          });
        }
      }

      return grouped;
    }
  }
};
</script>
