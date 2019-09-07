<template>
  <div>
    <li
      class="checkout-item"
      v-if="transferTypeCheckDelivery && transferTypeCheckPickup"
    >
      <b-form-group>
        <b-form-radio-group v-model="pickup" name="pickup">
          <b-form-radio :value="0" @click="pickup = 0">
            <strong>Delivery</strong>
          </b-form-radio>
          <b-form-radio :value="1" @click="pickup = 1">
            <strong>Pickup</strong>
          </b-form-radio>
        </b-form-radio-group>
      </b-form-group>
    </li>

    <li>
      <div>
        <p v-if="pickup === 0 && deliveryDaysOptions.length > 1">
          Delivery Day
        </p>
        <p v-if="pickup === 1 && deliveryDaysOptions.length > 1">
          Pickup Day
        </p>
        <b-form-group v-if="deliveryDaysOptions.length > 1" description>
          <b-select
            :options="deliveryDaysOptions"
            v-model="deliveryDay"
            @input="val => (deliveryDay = val)"
            class="delivery-select"
            required
          >
            <option slot="top" disabled>-- Select delivery day --</option>
          </b-select>
        </b-form-group>
        <div v-else-if="deliveryDaysOptions.length === 1">
          <h6 v-if="pickup === 0">
            Delivery Day: {{ deliveryDaysOptions[0].text }}
          </h6>
          <h6 v-if="pickup === 1">
            Pickup Day: {{ deliveryDaysOptions[0].text }}
          </h6>
        </div>
        <div v-if="storeModules.pickupLocations && pickup">
          <p>Pickup Location</p>
          <b-select
            v-model="selectedPickupLocation"
            :options="pickupLocationOptions"
            class="delivery-select mb-3"
            required
          ></b-select>
        </div>

        <div
          class="pt-2 pb-2"
          v-if="storeModules.transferHours && pickup === 1"
        >
          <strong>Pickup Time</strong>
          <b-form-select
            class="ml-2"
            v-model="transferTime"
            :options="transferTimeOptions"
          ></b-form-select>
        </div>
      </div>
    </li>

    <li
      class="checkout-item"
      v-if="minOption === 'meals' && total < minimumMeals && !manualOrder"
    >
      <p>Please add {{ remainingMeals }} {{ singOrPlural }} to continue.`</p>
    </li>

    <li
      class="checkout-item"
      v-if="
        minOption === 'price' && totalBagPricePreFees < minPrice && !manualOrder
      "
    >
      <p>
        Please add
        {{ format.money(remainingPrice, storeSettings.currency) }}
        more to continue.
      </p>
    </li>

    <li v-else-if="loggedIn">
      <div v-if="!willDeliver && !manualOrder && pickup != 1">
        <b-alert v-if="!loading" variant="danger center-text" show
          >You are outside of the delivery area.</b-alert
        >
      </div>
      <div v-else>
        <div v-if="manualOrder && !subscriptionId">
          <b-form-group>
            <h4 class="mt-2 mb-3">Choose Customer</h4>
            <b-select
              :options="customers"
              v-model="customer"
              class="bag-select"
              @change="getCards"
              required
            >
              <option slot="top" disabled>-- Select Customer --</option>
            </b-select>
          </b-form-group>
          <b-btn
            variant="primary"
            v-if="storeModules.manualCustomers"
            @click="showAddCustomerModal"
            >Add New Customer</b-btn
          >
        </div>
        <h4 class="mt-2 mb-3" v-if="!subscriptionId">
          Choose Payment Method
        </h4>
        <b-form-checkbox
          v-if="storeModules.cashOrders && !subscriptionId"
          v-model="cashOrder"
          class="pb-2 mediumCheckbox"
        >
          Cash
        </b-form-checkbox>
        <p
          v-if="
            cashOrder && creditCardList.length === 0 && creditCardId === null
          "
        >
          Please add a credit card on file in order to proceed with a cash
          order. In the event that cash is not paid, your credit card will be
          charged.
        </p>
        <card-picker
          :selectable="true"
          v-model="card"
          v-if="!manualOrder && !subscriptionId && !cashOrder"
          class="mb-3"
          ref="cardPicker"
        ></card-picker>
        <card-picker
          :selectable="true"
          v-model="card"
          v-if="
            !manualOrder &&
              !subscriptionId &&
              cashOrder &&
              creditCardId === null
          "
          class="mb-3"
          ref="cardPicker"
        ></card-picker>
        <card-picker
          :selectable="true"
          :creditCards="creditCardList"
          :manualOrder="true"
          v-model="cards"
          v-if="manualOrder && !cashOrder && !subscriptionId"
          class="mb-3"
          ref="cardPicker"
        ></card-picker>
        <b-btn
          v-if="
            creditCardId != null &&
              minOption === 'meals' &&
              total >= minMeals &&
              storeSettings.open &&
              !manualOrder &&
              !subscriptionId
          "
          @click="checkout"
          class="menu-bag-btn"
          >CHECKOUT</b-btn
        >
        <b-btn
          v-if="
            creditCardId != null &&
              minOption === 'price' &&
              totalBagPricePreFees >= minPrice &&
              storeSettings.open &&
              !manualOrder &&
              !subscriptionId
          "
          @click="checkout"
          class="menu-bag-btn"
          >CHECKOUT</b-btn
        >
        <div v-if="subscriptionId" class="d-none d-lg-block">
          <b-btn
            class="menu-bag-btn update-meals-btn"
            @click="updateSubscriptionMeals"
            >UPDATE MEALS</b-btn
          >
        </div>
        <div
          v-if="
            (manualOrder && cards.length > 0) || (cashOrder && customer != null)
          "
          class="row mt-4"
        >
          <div class="col-md-6" v-if="storeModules.deposits">
            <b-form-group v-if="manualOrder" horizontal label="Deposit %">
              <b-form-input
                v-model="deposit"
                type="text"
                required
                placeholder="Deposit %"
              ></b-form-input>
            </b-form-group>
          </div>
          <div class="col-md-6">
            <b-btn
              @click="checkout"
              v-if="storeModules.manualOrders"
              class="menu-bag-btn"
              >CHECKOUT</b-btn
            >
          </div>
        </div>
      </div>
    </li>

    <li v-else>
      <div class="row">
        <div class="col-md-6">
          <router-link
            :to="{
              path: '/login',
              query: { redirect: '/customer/bag' }
            }"
          >
            <b-btn class="menu-bag-btn">LOG IN</b-btn>
          </router-link>
        </div>
        <div class="col-md-6">
          <router-link
            :to="{
              path: '/register',
              query: { redirect: '/customer/bag' }
            }"
          >
            <b-btn class="menu-bag-btn">REGISTER</b-btn>
          </router-link>
        </div>
      </div>
    </li>
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
import SalesTax from "sales-tax";
import CardPicker from "../../components/Billing/CardPicker";

export default {
  components: {
    SalesTax,
    CardPicker
  },
  data() {
    return {
      salesTax: 0,
      stripeKey: window.app.stripe_key,
      loading: false,
      checkingOut: false,
      card: null
    };
  },
  props: {
    manualOrder: false,
    subscriptionId: null,
    cashOrder: false,
    mobile: false,
    pickup: 0,
    creditCardId: null,
    creditCard: {},
    creditCardList: {}
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
    transferType() {
      return this.storeSettings.transferType.split(",");
    },
    transferTypeCheckDelivery() {
      if (_.includes(this.transferType, "delivery")) return true;
    },
    transferTypeCheckPickup() {
      if (_.includes(this.transferType, "pickup")) return true;
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
    }
  },
  methods: {
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
          transferTime: this.transferTime
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
    }
  }
};
</script>
