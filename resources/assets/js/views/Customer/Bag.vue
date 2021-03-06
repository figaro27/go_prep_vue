<template>
  <div
    class="main-customer-container menu-width box-shadow top-fill"
    :style="fullHeight"
  >
    <div class="bag">
      <div
        class="alert alert-success"
        role="alert"
        v-if="
          store &&
            store.referral_settings &&
            store.referral_settings.enabled &&
            store.referral_settings.showInMenu &&
            user.referralUrlCode
        "
      >
        <p class="center-text strong">
          Give out your referral link to customers and if they order
          <span v-if="referralSettings.frequency == 'urlOnly'">
            using your link
          </span>
          you will receive {{ referralSettings.amountFormat }}
          <span v-if="referralSettings.kickbackType === 'credit'"
            >store credit</span
          >
          <span v-if="referralSettings.frequency == 'firstOrder'">
            on the first order they place.
          </span>
          <span v-else>
            on every future order they place.
          </span>
          <br />Your referral link is
          <a :href="referralUrl">{{ referralUrl }}</a>
        </p>
      </div>
      <auth-modal :showAuthModal="showAuthModal"></auth-modal>
      <spinner v-if="loading" position="absolute"></spinner>

      <b-alert
        show
        variant="warning"
        v-if="$route.query.sub === 'true' && subscriptions"
      >
        <p class="center-text strong">
          You have an active subscription with us. Update your items for your
          next renewal on
          {{ moment(subscriptions[0].next_renewal).format("dddd, MMM Do") }}.
        </p>
      </b-alert>

      <div class="row">
        <div class="col-md-5 mb-2 bag-actions">
          <above-bag
            :storeView="storeView || storeOwner"
            :manualOrder="manualOrder"
            :checkoutData="checkoutData"
            ref="aboveBag"
            class="mb-4"
          >
          </above-bag>
          <bag-area
            :pickup="pickup"
            :order="order"
            @updateLineItems="updateLineItems($event)"
            @updateData="updateData"
            ref="bagArea"
          ></bag-area>
          <bag-actions
            :manualOrder="manualOrder"
            :adjustOrder="adjustOrder"
            :adjustMealPlan="adjustMealPlan"
            :subscriptionId="subscriptionId"
            :orderId="orderId"
          ></bag-actions>
        </div>

        <div class="col-md-6 offset-md-1">
          <checkout-area
            :forceValue="forceValue"
            :manualOrder="manualOrder"
            :mobile="mobile"
            :subscriptionId="subscriptionId"
            :orderId="orderId"
            :cashOrder="cashOrder"
            :noBalance="noBalance"
            :hot="hot"
            :creditCardId="creditCardId"
            :creditCardList="creditCardList"
            :salesTax="salesTax"
            :customer="customer"
            :preview="preview"
            :deliveryDay="deliveryDay"
            :transferTime="transferTime"
            :pickup="pickup"
            :orderLineItems="orderLineItems"
            :checkoutData="checkoutData"
            :staffMember="staffMember"
            @updateData="updateData"
            :gateway="storeSettings.payment_gateway"
            :order="order"
            :adjustMealPlan="adjustMealPlan"
            ref="checkoutArea"
            :orderNotes="orderNotes"
            :publicOrderNotes="publicOrderNotes"
            :availablePromotionPoints="availablePromotionPoints"
          ></checkout-area>

          <store-closed
            v-if="!$route.params.storeView && !storeOwner"
          ></store-closed>
        </div>
      </div>
      <add-customer-modal
        :addCustomerModal="addCustomerModal"
      ></add-customer-modal>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import { createToken } from "vue-stripe-elements-plus";
import Register from "../Register";
import SalesTax from "sales-tax";

import MenuBag from "../../mixins/menuBag";
import states from "../../data/states.js";

import AboveBag from "../../components/Customer/AboveBag";
import BagArea from "../../components/Customer/BagArea";
import CheckoutArea from "../../components/Customer/CheckoutArea";
import AddCustomerModal from "../../components/Customer/AddCustomerModal";
import BagActions from "../../components/Customer/BagActions";
import AuthModal from "../../components/Customer/AuthModal";
import StoreClosed from "../../components/Customer/StoreClosed";
import store from "../../store";

export default {
  components: {
    cSwitch,
    Register,
    AboveBag,
    BagArea,
    CheckoutArea,
    AddCustomerModal,
    SalesTax,
    BagActions,
    AuthModal,
    StoreClosed
  },
  props: {
    storeView: false,
    manualOrder: false,
    forceValue: false,
    //subscriptionId: null,
    adjustOrder: false,
    adjustMealPlan: false,
    preview: false,
    orderId: null,
    order: null,
    checkoutDataProp: null,
    adjustMealPlan: null,
    subscription: null
  },
  mixins: [MenuBag],
  data() {
    return {
      availablePromotionPoints: null,
      orderNotes: null,
      publicOrderNotes: null,
      showAuthModal: false,
      //couponFreeDelivery: 0,
      cashOrder: null,
      noBalance: null,
      hot: null,
      addCustomerModal: false,
      deposit: 100,
      creditCardList: [],
      creditCard: {},
      creditCardId: null,
      customer:
        this.checkoutDataProp && this.checkoutDataProp.customer
          ? this.checkoutDataProp.customer
          : null,
      selectedPickupLocation: null,
      pickup: 0,
      deliveryDay:
        this.checkoutDataProp && this.checkoutDataProp.deliveryDay
          ? this.checkoutDataProp.deliveryDay
          : null,
      stripeKey: window.app.stripe_key,
      loading: false,
      checkingOut: false,
      couponCode: "",
      couponClass: "checkout-item",
      deliveryFee: 0,
      gratuity: null,
      amounts: {},
      salesTax: 0,
      orderLineItems: null,
      checkoutData: this.checkoutDataProp
    };
  },
  watch: {
    deliveryDateOptions(val) {
      if (!this.deliveryDay && val[0]) {
        // this.deliveryDay = val[0].value;
      }
    },
    subscriptions: function() {
      if (
        this.user.id &&
        this.store.modules.subscriptionOnly &&
        this.subscriptions.length > 0 &&
        !this.store.settings.allowMultipleSubscriptions
      ) {
        this.$router.push({
          path: "/customer/subscriptions/" + this.subscriptions[0].id
        });
      }
    }
  },
  created() {
    this.$eventBus.$on("showAuthModal", () => {
      this.showAuthModal = true;
    });
  },
  computed: {
    ...mapGetters({
      creditCards: "cards",
      context: "context",
      store: "viewedStore",
      storeModules: "viewedStoreModules",
      storeModuleSettings: "viewedStoreModuleSettings",
      storeCustomers: "storeCustomers",
      storeSettings: "viewedStoreSetting",
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
      referrals: "viewedStoreReferrals",
      pickupLocations: "viewedStorePickupLocations",
      lineItems: "viewedStoreLineItems",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      _orders: "orders",
      user: "user",
      bagDeliverySettings: "bagDeliverySettings",
      subscriptions: "subscriptions",
      bagPickup: "bagPickup",
      bagTransferTime: "bagTransferTime",
      bagStaffMember: "bagStaffMember"
    }),
    transferTime() {
      return this.$route.params.adjustOrder
        ? this.$route.params.transferTime
        : this.bagTransferTime;
    },
    staffMember() {
      return this.$route.params.adjustOrder
        ? this.$route.params.staffMember
        : this.bagStaffMember;
    },
    fullHeight() {
      if (!this.mobile && !this.storeOwner) return "min-height:100%";
    },
    storeOwner() {
      let flag = false;
      if (this.user && this.user.storeOwner) {
        flag = true;
      }

      return flag;
    },
    couponFreeDelivery() {
      return this.coupon ? this.coupon.freeDelivery : 0;
    },
    couponApplied() {
      return !_.isNull(this.coupon);
    },
    customers() {
      let customers = this.storeCustomers;
      if (_.isEmpty(customers)) {
        return [];
      }

      let grouped = {};
      customers.forEach(customer => {
        grouped[customer.id] = customer.name;
      });
      return grouped;
    },
    storeId() {
      return this.store.id;
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
    storeSettings() {
      return this.store.settings;
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
    minimumOption() {
      return this.minOption;
    },
    minimumMeals() {
      return this.minMeals;
    },
    minimumPrice() {
      return this.minPrice;
    },
    remainingMeals() {
      return this.minMeals - this.total;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    },
    subtotal() {
      let totalLineItemsPrice = 0;
      this.orderLineItems.forEach(orderLineItem => {
        totalLineItemsPrice += orderLineItem.price * orderLineItem.quantity;
      });
      let subtotal = this.totalBagPricePreFees + totalLineItemsPrice;
      return subtotal;
    },
    couponReduction() {
      if (!this.couponApplied) {
        return 0;
      }
      let coupon = this.coupon;
      let subtotal = this.subtotal;
      if (coupon.type === "flat") {
        return coupon.amount;
      } else if (coupon.type === "percent") {
        return (coupon.amount / 100) * subtotal;
      }
    },
    afterCoupon() {
      if (this.couponApplied) {
        let subtotal = this.subtotal - this.couponReduction;
        return subtotal;
      } else return this.subtotal;
    },
    mealPlanDiscount() {
      return this.subtotal * (this.storeSettings.mealPlanDiscount / 100);
    },
    afterDiscount() {
      if (this.applyMealPlanDiscount && this.deliveryPlan) {
        return this.afterCoupon - this.mealPlanDiscount;
      } else return this.afterCoupon;
    },
    deliveryFeeAmount() {
      if (!this.pickup) {
        let {
          applyDeliveryFee,
          deliveryFee,
          deliveryFeeType,
          mileageBase,
          mileagePerMile
        } = this.bagDeliverySettings;

        if (!this.couponFreeDelivery) {
          if (applyDeliveryFee) {
            if (deliveryFeeType === "flat") {
              return deliveryFee;
            } else if (deliveryFeeType === "mileage") {
              mileageBase = parseFloat(mileageBase);
              mileagePerMile = parseFloat(mileagePerMile);
              let distance = parseFloat(this.store.distance);
              return mileageBase + mileagePerMile * distance;
            }
          } else return 0;
        } else return 0;
      } else return 0;
    },
    processingFeeAmount() {
      if (this.storeSettings.processingFeeType === "flat") {
        return this.storeSettings.processingFee;
      } else if (this.storeSettings.processingFeeType === "percent") {
        return (this.storeSettings.processingFee / 100) * this.subtotal;
      }
    },
    afterFees() {
      let {
        applyDeliveryFee,
        deliveryFee,
        deliveryFeeType,
        mileageBase,
        mileagePerMile
      } = this.bagDeliverySettings;

      let applyProcessingFee = this.storeSettings.applyProcessingFee;
      let processingFee = this.processingFeeAmount;
      let subtotal = this.afterDiscount;

      if (applyDeliveryFee & (this.pickup === 0))
        subtotal += this.deliveryFeeAmount;
      if (applyProcessingFee) subtotal += processingFee;

      return subtotal;
    },
    grandTotal() {
      let subtotal = this.afterFees;
      let tax = 1;

      if (this.storeSettings.enableSalesTax) {
        tax = 1 + this.salesTax;
      }
      return subtotal * tax;
    },
    hasCoupons() {
      if (this.coupons.length > 0) {
        return true;
      } else {
        return false;
      }
    },
    applyMealPlanDiscount() {
      return this.storeSettings.applyMealPlanDiscount;
    },
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "items";
      }
      return "item";
    },
    singOrPluralTotal() {
      if (this.total > 1) {
        return "Items";
      }
      return "Item";
    },
    deliveryPlanText() {
      if (this.deliveryPlan) return "Prepared Weekly";
      else return "Prepared Once";
    },
    tax() {
      if (this.storeSettings.enableSalesTax)
        return this.salesTax * this.afterFees;
      else return 0;
    },
    stateNames() {
      return states.stateNames();
    },
    subscriptionId() {
      if (this.$route.query.subscriptionId) {
        return this.$route.query.subscriptionId;
      }
      return this.$route.params.subscriptionId;
    },
    referralSettings() {
      return this.store.referral_settings;
    },
    referralAmount() {
      return this.store.referral_settings.amountFormat;
    },
    referralUrl() {
      return this.store.referral_settings.url + this.user.referralUrlCode;
    }
  },
  watch: {
    subscriptions: function() {
      if (
        this.user.id &&
        this.subscriptions.length > 0 &&
        !this.$route.params.id &&
        !this.store.settings.allowMultipleSubscriptions
      ) {
        this.$router.push({
          path: "/customer/bag",
          params: { subscriptionId: this.subscriptions[0].id },
          query: { sub: true, subscriptionId: this.subscriptions[0].id }
        });
      }
    }
  },
  mounted() {
    if (this.store.modules.pickupOnly && this.context !== "store") {
      this.setBagPickup(1);
    }
    if (this.subscriptionId) {
      let sub = _.find(this.subscriptions, subscription => {
        return subscription.id === parseInt(this.subscriptionId);
      });
      if (sub) {
        this.pickup = sub.pickup;
      }
    }
    $([document.documentElement, document.body]).scrollTop(0);
    if (this.$route.params.adjustOrder && this.order.coupon_id !== null) {
      axios
        .post(this.prefix + "findCouponById", {
          store_id: this.store.id,
          couponId: this.order.coupon_id
        })
        .then(resp => {
          if (resp.data) {
            this.setBagCoupon(resp.data);
          }
        });
    }

    if (
      this.$route.params.adjustOrder &&
      this.order.applied_referral_id !== null
    ) {
      let appliedReferral = this.referrals.find(referral => {
        return referral.id === this.order.applied_referral_id;
      });
      this.setBagReferral(appliedReferral);
    }

    if (this.store.modules.subscriptionOnly) {
      this.$refs.checkoutArea.weeklySubscriptionValue = true;
    }
    if (
      this.$route.params.storeView &&
      this.storeModules.cashOrders &&
      this.storeModules.cashOrderAutoSelect
    ) {
      this.cashOrder = true;
    }

    if (this.storeModules.cashOrderNoBalance) {
      this.noBalance = true;
    }

    if (this.order && this.order.hot) {
      this.hot = true;
    }

    if (this.$route.params.adjustOrder) {
      this.deliveryDay = this.$route.params.deliveryDay;
    }

    if (this.$route.params.pickup != undefined) {
      this.pickup = this.$route.params.pickup;
    } else if (
      this.storeModules.hideDeliveryOption &&
      !this.$route.params.storeView &&
      !this.storeOwner
    ) {
      this.pickup = 1;
    } else {
      this.setPickup();
    }

    this.creditCardId = this.card;

    SalesTax.getSalesTax("US", this.store.details.state).then(tax => {
      this.setSalesTax(tax.rate);
    });

    if (!_.includes(this.transferType, "delivery")) this.pickup = 1;

    if (this.storeModules.pickupLocations && this.pickupLocationOptions)
      this.selectedPickupLocation = this.pickupLocationOptions[0].value;

    // if (!this.deliveryDay && this.deliveryDateOptions.length > 0) {
    //   this.deliveryDay = this.deliveryDateOptions[0].value;
    // }

    if (!this.$route.params.storeView && this.loggedIn) {
      this.getPromotionPoints(this.user.id);
    }

    if (this.$route.params.subscription) {
      this.customer = this.$route.params.subscription.customer_id;
    }
  },
  updated() {
    this.creditCardId = this.card;
  },
  methods: {
    updateData(newData) {
      if (this.forceValue || !this.$route.params.storeView) {
        this.checkoutData = newData;
        if (newData.hasOwnProperty("customer")) {
          this.customer = newData.customer;
        }

        if (newData.hasOwnProperty("pickup")) {
          this.pickup = newData.pickup;
        }

        if (newData.hasOwnProperty("deliveryDay")) {
          this.deliveryDay = newData.deliveryDay;
        }

        if (newData.hasOwnProperty("cashOrder")) {
          this.cashOrder = newData.cashOrder;
        }

        if (newData.hasOwnProperty("noBalance")) {
          this.noBalance = newData.noBalance;
        }

        if (newData.hasOwnProperty("hot")) {
          this.hot = newData.hot;
        }

        if (newData.hasOwnProperty("creditCardList")) {
          this.creditCardList = newData.creditCardList;
        }
      }
    },
    setSalesTax(rate) {
      this.salesTax = rate;
    },
    ...mapActions([
      "refreshSubscriptions",
      "refreshStoreSubscriptions",
      "refreshCustomerOrders",
      "refreshOrders",
      "refreshStoreSubscriptions",
      "refreshUpcomingOrders",
      "refreshStoreCustomers",
      "refreshCards"
    ]),
    ...mapMutations([
      "emptyBag",
      "setBagMealPlan",
      "setBagCoupon",
      "setBagReferral",
      "setBagPickup"
    ]),
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    checkout() {
      if (this.checkingOut) {
        return;
      }

      // Ensure delivery day is set
      if (!this.deliveryDay && this.deliveryDateOptions) {
        this.deliveryDay = this.deliveryDateOptions[0].value;
      } else if (!this.deliveryDateOptions) {
        return;
      }

      // this.loading = true;
      this.checkingOut = true;

      this.deliveryFee = this.deliveryFeeAmount;
      if (this.pickup === 0) {
        this.selectedPickupLocation = null;
      }

      let deposit = this.deposit;
      if (deposit.toString().includes("%")) {
        deposit.replace("%", "");
        deposit = parseInt(deposit);
      }

      let endPoint = "";
      if (this.manualOrder === true) {
        endPoint = "/api/me/checkout";
      } else {
        endPoint = "/api/bag/checkout";
      }

      let cardId = this.card;

      if (this.cashOrder === true) {
        cardId = 0;
      }
      axios
        .post(endPoint, {
          subtotal: this.subtotal,
          lineItemTotal: this.lineItemTotal,
          afterDiscount: this.afterDiscount,
          bag: this.bag,
          plan: this.deliveryPlan,
          pickup: this.pickup,
          delivery_day: this.deliveryDay,
          card_id: cardId,
          store_id: this.store.id,
          salesTax: this.tax,
          coupon_id: this.couponApplied ? this.coupon.id : null,
          couponReduction: this.couponReduction,
          couponCode: this.couponApplied ? this.coupon.code : null,
          deliveryFee: this.deliveryFee,
          gratuity: this.gratuity,
          pickupLocation: this.selectedPickupLocation,
          customer: this.customer,
          deposit: deposit,
          cashOrder: this.cashOrder,
          transferTime: this.transferTime,
          lineItemsOrder: this.orderLineItems
        })
        .then(async resp => {
          this.emptyBag();
          let weeklyDelivery = this.deliveryPlan;
          this.setBagMealPlan(false);
          this.setBagCoupon(null);

          if (this.manualOrder && weeklyDelivery) {
            this.refreshStoreSubscriptions();
            this.$router.push({
              path: "/store/meal-plans"
            });
            return;
          } else if (this.manualOrder && !weeklyDelivery) {
            this.refreshUpcomingOrders();
            this.$router.push({
              path: "/store/orders"
            });
            return;
          }
          if (weeklyDelivery) {
            await this.refreshSubscriptions();
            this.$router.push({
              path: "/customer/meal-plans",
              query: { created: true, pickup: this.pickup }
            });
          } else {
            await this.refreshCustomerOrders();
            this.$router.push({
              path: "/customer/orders",
              query: { created: true, pickup: this.pickup }
            });
          }
        })
        .catch(response => {
          let error = _.first(Object.values(response.response.data.errors)) || [
            "Please try again"
          ];
          error = error.join(" ");
          this.$toastr.w(error, "Error");
        })
        .finally(() => {
          this.loading = false;
          this.checkingOut = false;
        });
    },
    // applyCoupon() {
    //   this.coupons.forEach(coupon => {
    //     if (this.couponCode.toUpperCase() === coupon.code.toUpperCase()) {
    //       if (coupon.oneTime) {
    //         let oneTimePass = this.oneTimeCouponCheck(coupon.id);
    //         if (oneTimePass === "login") {
    //           this.$toastr.w(
    //             "This is a one-time coupon. Please log in or create an account to check if it has already been used."
    //           );
    //           return;
    //         }
    //         if (!oneTimePass) {
    //           this.$toastr.w(
    //             "This was a one-time coupon that has already been used.",
    //             'Coupon Code: "' + this.couponCode + '"'
    //           );
    //           this.couponCode = "";
    //           return;
    //         }
    //       }
    //       this.coupon = coupon;
    //       this.setBagCoupon(coupon);
    //       this.couponCode = "";
    //       this.$toastr.s("Coupon Applied.", "Success");
    //     }
    //   });
    // },
    oneTimeCouponCheck(couponId) {
      if (!this.loggedIn) {
        return "login";
      }
      let couponCheck = true;
      this._orders.forEach(order => {
        if (couponId === order.coupon_id) {
          couponCheck = false;
        }
      });
      return couponCheck;
    },
    getCards() {
      this.refreshCards();
    },
    getCustomer() {
      return this.customer;
    },
    showAddCustomerModal() {
      this.addCustomerModal = true;
    },
    addCustomer() {
      let form = this.form;

      if (!form.accepted_tos) {
        this.$toastr.w(
          "Please accept the terms of service.",
          "Registration failed"
        );
        return;
      }

      axios
        .post("/api/me/register", form)
        .then(async response => {
          this.addCustomerModal = false;
          this.form = {};
          await this.refreshStoreCustomers();
        })
        .catch(e => {
          this.$toastr.w("Please try again.", "Registration failed");
        });
    },
    setCustomer(id) {
      if (id) {
        this.customer = id;
      } else {
        this.customer = this.storeCustomers.slice(-1)[0].id;
      }
    },
    changeState(state) {
      this.form.state = state.abbreviation;
    },
    updateLineItems(orderLineItems) {
      this.orderLineItems = orderLineItems;
    },
    setPickup() {
      if (
        this.$refs.checkoutArea &&
        this.$refs.checkoutArea.checkPickup() === true
      ) {
        this.pickup = 1;
      }
    },
    getPromotionPoints(id) {
      axios
        .post("/api/me/getPromotionPoints", {
          userId: id,
          storeId: this.store.id
        })
        .then(response => {
          this.availablePromotionPoints = response.data;
        });
    }
  }
};
</script>
