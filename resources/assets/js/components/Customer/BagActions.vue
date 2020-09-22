<template>
  <div>
    <div>
      <div class="row" v-if="!bagView">
        <div class="col-md-7">
          <p
            class="mt-2 ml-2"
            style="margin-bottom:0;"
            v-if="!minimumMet && !storeView"
          >
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
    weeklySubscriptionValue: null,
    lineItemOrders: null
  },
  data() {
    return {
      minimumDeliveryDayAmount: 0
    };
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
    subId() {
      return this.$route.params.subscriptionId
        ? this.$route.params.subscriptionId
        : this.$route.query.subscriptionId;
    },
    subscription() {
      return this.$parent.subscription;
    },
    isMultipleDelivery() {
      return this.storeModules.multipleDeliveryDays == 1 ? true : false;
    },
    brandColor() {
      let style = "background-color:";
      style += this.store.settings.color;
      return style;
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
        return "items";
      }
      return "item";
    },
    minimumMet() {
      if (this.isMultipleDelivery) {
        if (this.minimumDeliveryDayAmount > 0) {
          let groupTotal = [];
          this.groupBag.forEach((group, index) => {
            groupTotal[index] = 0;
            group.items.forEach(item => {
              groupTotal[index] += item.price * item.quantity;
            });
          });

          if (
            groupTotal.every(item => {
              return item > this.minimumDeliveryDayAmount;
            })
          ) {
            return true;
          } else {
            return false;
          }
        }
      }
      let giftCardOnly = true;
      this.bag.forEach(item => {
        if (!item.meal.gift_card) {
          giftCardOnly = false;
        }
      });
      if (this.bag.length == 0) {
        giftCardOnly = false;
      }

      if (
        (this.minOption === "meals" && this.total >= this.minMeals) ||
        (this.minOption === "price" &&
          this.totalBagPricePreFees >= this.minPrice) ||
        this.store.settings.minimumDeliveryOnly ||
        giftCardOnly
      )
        return true;
      else return false;
    },
    addMore() {
      if (this.isMultipleDelivery) {
        if (this.minimumDeliveryDayAmount > 0) {
          let groupTotal = [];
          this.groupBag.forEach((group, index) => {
            groupTotal[index] = 0;
            group.items.forEach(item => {
              groupTotal[index] += item.price * item.quantity;
            });
          });

          if (
            !groupTotal.every(item => {
              return item > this.minimumDeliveryDayAmount;
            })
          ) {
            return (
              "Please add at least " +
              format.money(
                this.minimumDeliveryDayAmount,
                this.storeSettings.currency
              ) +
              " for each day."
            );
          }
        }
      }

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
  },
  mounted() {
    if (this.isMultipleDelivery) {
      this.minimumDeliveryDayAmount = this.store.delivery_days[0].minimum;
    }
  }
};
</script>
