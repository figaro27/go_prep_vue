<template>
  <div class="main-customer-container box-shadow top-fill">
    <div class="bag">
      <auth-modal :showAuthModal="showAuthModal"></auth-modal>
      <spinner v-if="loading" position="absolute"></spinner>
      <div class="row">
        <div class="col-md-5 mb-2 bag-actions">
          <above-bag
            :storeView="storeView || storeOwner"
            :manualOrder="manualOrder"
            :checkoutData="checkoutData"
            class="mb-4"
          >
          </above-bag>
          <bag-area
            :pickup="pickup"
            :order="order"
            @updateLineItems="updateLineItems($event)"
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
            @updateData="updateData"
            :gateway="storeSettings.payment_gateway"
            :order="order"
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
    checkoutDataProp: null
  },
  mixins: [MenuBag],
  data() {
    return {
      showAuthModal: false,
      //couponFreeDelivery: 0,
      transferTime: "",
      cashOrder: null,
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
      transferTime: null,
      deliveryDay: null,
      stripeKey: window.app.stripe_key,
      loading: false,
      checkingOut: false,
      couponCode: "",
      couponClass: "checkout-item",
      deliveryFee: 0,
      amounts: {},
      salesTax: 0,
      orderLineItems: null,
      checkoutData: this.checkoutDataProp
    };
  },
  watch: {
    deliveryDaysOptions(val) {
      if (!this.deliveryDay && val[0]) {
        this.deliveryDay = val[0].value;
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
      pickupLocations: "viewedStorePickupLocations",
      lineItems: "viewedStoreLineItems",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      _orders: "orders",
      user: "user"
    }),
    storeOwner() {
      let flag = false;
      if (this.user && this.user.storeOwner) {
        flag = true;
      }

      return flag;
    },
    order() {
      return this.$route.params.order;
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
        if (!this.couponFreeDelivery) {
          if (this.storeSettings.applyDeliveryFee) {
            if (this.storeSettings.deliveryFeeType === "flat") {
              return this.storeSettings.deliveryFee;
            } else if (this.storeSettings.deliveryFeeType === "mileage") {
              let mileageBase = parseFloat(this.storeSettings.mileageBase);
              let mileagePerMile = parseFloat(
                this.storeSettings.mileagePerMile
              );
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
      let applyDeliveryFee = this.storeSettings.applyDeliveryFee;
      let applyProcessingFee = this.storeSettings.applyProcessingFee;
      let deliveryFee = this.deliveryFeeAmount;
      let processingFee = this.processingFeeAmount;
      let subtotal = this.afterDiscount;

      if (applyDeliveryFee & (this.pickup === 0)) subtotal += deliveryFee;
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
        return "meals";
      }
      return "meal";
    },
    singOrPluralTotal() {
      if (this.total > 1) {
        return "Meals";
      }
      return "Meal";
    },
    deliveryPlanText() {
      if (this.deliveryPlan) return "Prepared Weekly";
      else return "Prepared Once";
    },
    deliveryDaysOptions() {
      return (this.storeSettings.next_orderable_delivery_dates, []).map(
        date => {
          return {
            value: date.date,
            text: moment(date.date).format("dddd MMM Do")
          };
        }
      );
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
      return this.$route.params.subscriptionId;
    }
  },
  mounted() {
    if (this.context == "customer" || this.context == "guest") {
      store.dispatch("refreshStoreMeals");
    }

    this.deliveryDay = this.$route.params.deliveryDay;
    this.transferTime = this.$route.params.transferTime;
    if (this.$route.params.pickup != undefined) {
      this.pickup = this.$route.params.pickup;
    } else if (
      this.storeModules.hideDeliveryOption &&
      (!this.$route.params.storeView && !this.storeOwner)
    ) {
      this.pickup = 1;
    } else {
      this.pickup = 0;
    }

    this.creditCardId = this.card;

    SalesTax.getSalesTax("US", this.store.details.state).then(tax => {
      this.setSalesTax(tax.rate);
    });

    if (!_.includes(this.transferType, "delivery")) this.pickup = 1;

    if (this.storeModules.pickupLocations)
      this.selectedPickupLocation = this.pickupLocationOptions[0].value;

    if (!this.deliveryDay && this.deliveryDaysOptions.length > 0) {
      this.deliveryDay = this.deliveryDaysOptions[0].value;
    }
  },
  updated() {
    this.creditCardId = this.card;
  },
  methods: {
    updateData(newData) {
      if (this.forceValue) {
        console.log("updating", newData);

        this.checkoutData = newData;
        if (newData.hasOwnProperty("customer")) {
          this.customer = newData.customer;
        }

        if (newData.hasOwnProperty("pickup")) {
          this.pickup = newData.pickup;
        }

        if (newData.hasOwnProperty("transferTime")) {
          this.transferTime = newData.transferTime;
        }

        if (newData.hasOwnProperty("deliveryDay")) {
          this.deliveryDay = newData.deliveryDay;
        }

        if (newData.hasOwnProperty("cashOrder")) {
          this.cashOrder = newData.cashOrder;
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
      "refreshStoreCustomers"
    ]),
    ...mapMutations(["emptyBag", "setBagMealPlan", "setBagCoupon"]),
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
      if (!this.deliveryDay && this.deliveryDaysOptions) {
        this.deliveryDay = this.deliveryDaysOptions[0].value;
      } else if (!this.deliveryDaysOptions) {
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
          this.$toastr.e(error, "Error");
        })
        .finally(() => {
          this.loading = false;
          this.checkingOut = false;
        });
    },
    applyCoupon() {
      this.coupons.forEach(coupon => {
        if (this.couponCode.toUpperCase() === coupon.code.toUpperCase()) {
          if (coupon.oneTime) {
            let oneTimePass = this.oneTimeCouponCheck(coupon.id);
            if (oneTimePass === "login") {
              this.$toastr.e(
                "This is a one-time coupon. Please log in or create an account to check if it has already been used."
              );
              return;
            }
            if (!oneTimePass) {
              this.$toastr.e(
                "This was a one-time coupon that has already been used.",
                'Coupon Code: "' + this.couponCode + '"'
              );
              this.couponCode = "";
              return;
            }
          }
          this.coupon = coupon;
          this.setBagCoupon(coupon);
          this.couponCode = "";
          this.$toastr.s("Coupon Applied.", "Success");
        }
      });
    },
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
      this.creditCardId = null;
      this.creditCards = null;
      this.$nextTick(() => {
        axios
          .post("/api/me/getCards", {
            id: this.customer
          })
          .then(response => {
            this.creditCardList = response.data;

            if (response.data.length) {
              this.creditCardId = response.data[0].id;
              this.creditCard = response.data[0];
              this.$cardPicker.setCard(response.data[0].id);
            }
          });
      });
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
        this.$toastr.e(
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
          this.$toastr.e("Please try again.", "Registration failed");
        });
    },
    setCustomer() {
      this.customer = this.storeCustomers.slice(-1)[0].id;
    },
    changeState(state) {
      this.form.state = state.abbreviation;
    },
    updateLineItems(orderLineItems) {
      this.orderLineItems = orderLineItems;
    }
  }
};
</script>
