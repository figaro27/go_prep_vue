<template>
  <div>
    <ul class="list-group">
      <li
        class="bag-item"
        v-if="
          // Hiding until subscriptions are fixed.
          1 === 2 &&
            storeSettings.allowMealPlans &&
            $route.params.subscriptionId === undefined &&
            $parent.orderId === undefined
        "
      >
        <div class="row" v-if="!manualOrder">
          <div class="col-md-12 pb-1">
            <h3>
              <img
                v-if="!mobile"
                v-b-popover.hover.bottom="
                  'Choose a weekly subscription instead of a one time order and meals will be given to you on a weekly basis. You can swap out meals as well as pause or cancel the subscription at any time. This will apply to the following week\'s renewal.'
                "
                title="Weekly Subscription"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
              <img
                v-if="mobile"
                v-b-popover.click.top="
                  'Choose a weekly subscription instead of a one time order and meals will be given to you on a weekly basis. You can swap out meals as well as pause or cancel the subscription at any time. This will apply to the following week\'s renewal.'
                "
                title="Weekly Subscription"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
              <strong
                >Subscribe
                <span
                  v-if="
                    storeSettings.mealPlanDiscount > 0 &&
                      storeSettings.mealPlanDiscount > 0
                  "
                  >& Save</span
                >
                <span
                  v-if="
                    storeSettings.applyMealPlanDiscount &&
                      storeSettings.mealPlanDiscount > 0
                  "
                  >{{ storeSettings.mealPlanDiscount }}%</span
                ></strong
              >
            </h3>
          </div>
        </div>
        <div class="row">
          <div class="col-md-9">
            <strong
              ><p class="mr-1">
                <span
                  v-if="
                    storeSettings.applyMealPlanDiscount &&
                      storeSettings.mealPlanDiscount > 0
                  "
                >
                  Subscribe & save
                  <span class="text-success standout">{{
                    format.money(mealPlanDiscount, storeSettings.currency)
                  }}</span>
                  on each order.
                </span>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  :checked="weeklySubscription"
                  class="pt-3"
                  @change="
                    val => {
                      setWeeklySubscriptionValue(val);
                      updateParentData();

                      setBagMealPlan(val);
                    }
                  "
                /></p
            ></strong>
          </div>
        </div>
      </li>
      <li class="checkout-item">
        <p>
          <strong>
            {{ total }} {{ singOrPluralTotal }}
            {{ deliveryPlanText }}
          </strong>
        </p>
      </li>
      <li class="checkout-item">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Subtotal:</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            {{ format.money(subtotal, storeSettings.currency) }}
          </div>
        </div>
      </li>
      <li class="checkout-item" v-if="couponApplied">
        <div class="row">
          <div class="col-6 col-md-4">
            <span class="d-inline mr-2" @click="removeCoupon">
              <img class="couponX" src="/images/customer/x.png" />
            </span>
            <span class="text-success">({{ coupon.code }})</span>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <span class="text-success" v-if="couponReduction > 0"
              >({{
                format.money(couponReduction, storeSettings.currency)
              }})</span
            >
            <span
              class="text-success"
              v-if="couponReduction > 0 && couponFreeDelivery"
            >
              +
            </span>
            <span class="text-success" v-if="couponFreeDelivery"
              >Free Delivery</span
            >
          </div>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="weeklySubscription && applyMealPlanDiscount"
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Subscription Discount:</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5 text-success">
            ({{ format.money(mealPlanDiscount, storeSettings.currency) }})
          </div>
        </div>
      </li>
      <li class="checkout-item" v-if="storeSettings.enableSalesTax">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Sales Tax:</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            {{ format.money(tax, storeSettings.currency) }}
          </div>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          storeSettings.applyDeliveryFee && pickup === 0 && !couponFreeDelivery
        "
      >
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Delivery Fee:</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            {{ format.money(deliveryFeeAmount, storeSettings.currency) }}
          </div>
        </div>
      </li>
      <li class="checkout-item" v-if="storeSettings.applyProcessingFee">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Processing Fee:</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            {{ format.money(processingFeeAmount, storeSettings.currency) }}
          </div>
        </div>
      </li>

      <li class="checkout-item">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Total</strong>
          </div>
          <div class="col-6 col-md-3 offset-md-5">
            <strong>{{
              format.money(grandTotal, storeSettings.currency)
            }}</strong>
          </div>
        </div>
      </li>

      <li v-if="hasCoupons">
        <div class="row">
          <div class="col-xs-6 pl-3">
            <b-form-group id="coupon">
              <b-form-input
                id="coupon-code"
                v-model="couponCode"
                required
                placeholder="Enter Coupon Code"
              ></b-form-input>
            </b-form-group>
          </div>
          <div class="col-xs-6 pl-2">
            <b-btn variant="primary" @click="applyCoupon">Apply</b-btn>
          </div>
        </div>
      </li>
    </ul>
    <li
      class="checkout-item"
      v-if="
        transferTypeCheckDelivery &&
          transferTypeCheckPickup &&
          (!storeModules.hideDeliveryOption || $route.params.storeView === true)
      "
    >
      <b-form-group>
        <b-form-radio-group v-model="pickup" v-on:input="changePickupV">
          <b-form-radio :value="0">
            <strong>Delivery</strong>
          </b-form-radio>
          <b-form-radio :value="1">
            <strong>Pickup</strong>
          </b-form-radio>
        </b-form-radio-group>
      </b-form-group>
    </li>
    <div v-if="!storeModules.hideTransferOptions || $route.params.storeView">
      <li class="checkout-item" v-if="$route.params.storeView">
        <div>
          <strong v-if="pickup === 0">Delivery Day</strong>
          <strong v-if="pickup === 1">Pickup Day</strong>
          <b-select
            :options="deliveryDaysOptionsStoreView"
            v-model="deliveryDay"
            @input="changeDeliveryDay"
            class="delivery-select ml-2"
            required
          >
            <option slot="top" disabled>-- Select delivery day --</option>
          </b-select>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          deliveryDaysOptions.length > 1 &&
            $route.params.subscriptionId === undefined &&
            !$route.params.storeView
        "
      >
        <div>
          <strong
            v-if="
              pickup === 0 &&
                deliveryDaysOptions.length > 1 &&
                !storeModules.hideDeliveryOption
            "
          >
            Delivery Day
          </strong>
          <strong v-if="pickup === 1 && deliveryDaysOptions.length > 1">
            Pickup Day
          </strong>
          <b-select
            v-if="deliveryDaysOptions.length > 1"
            :options="deliveryDaysOptions"
            v-model="deliveryDay"
            @input="changeDeliveryDay"
            class="delivery-select ml-2"
            required
          >
            <option slot="top" disabled>-- Select delivery day --</option>
          </b-select>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          deliveryDaysOptions.length === 1 &&
            $route.params.subscriptionId === undefined &&
            !$route.params.storeView
        "
      >
        <div>
          <strong v-if="pickup === 0">
            Delivery Day: {{ deliveryDaysOptions[0].text }}
          </strong>
          <strong v-if="pickup === 1">
            Pickup Day: {{ deliveryDaysOptions[0].text }}
          </strong>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          storeModules.pickupHours &&
            pickup &&
            $route.params.subscriptionId === undefined
        "
      >
        <div>
          <strong>Pickup Time</strong>
          <b-form-select
            class="delivery-select ml-2"
            v-model="transferTime"
            :options="transferTimeOptions"
          ></b-form-select>
        </div>
      </li>
      <li
        class="checkout-item"
        v-if="
          storeModules.deliveryHours &&
            !pickup &&
            $route.params.subscriptionId === undefined
        "
      >
        <div>
          <strong>Delivery Time</strong>
          <b-form-select
            class="delivery-select ml-2"
            v-model="transferTime"
            :options="transferTimeOptions"
          ></b-form-select>
        </div>
      </li>
    </div>

    <li
      class="checkout-item"
      v-if="
        $parent.orderId === undefined &&
          storeModules.pickupLocations &&
          pickup &&
          $route.params.subscriptionId === undefined
      "
    >
      <div>
        <strong>Pickup Location</strong>
        <b-select
          v-model="selectedPickupLocation"
          :options="pickupLocationOptions"
          class="delivery-select mb-3 ml-2"
          required
        ></b-select>
      </div>
    </li>

    <li v-if="loggedIn">
      <div
        v-if="
          !willDeliver &&
            !manualOrder &&
            pickup != 1 &&
            !$route.params.storeView
        "
      >
        <b-alert
          v-if="!loading && !$route.params.storeView"
          variant="warning center-text"
          show
          >You are outside of the delivery area.</b-alert
        >
      </div>
      <div>
        <div v-if="$route.params.manualOrder">
          <b-form-group>
            <h4 class="mt-2 mb-3">Choose Customer</h4>
            <v-select
              label="text"
              :options="customers"
              :reduce="customer => customer.value"
              v-model="customer"
              :value="customer"
              @input="getCards"
            >
            </v-select>
          </b-form-group>
          <b-btn
            variant="primary"
            v-if="storeModules.manualCustomers"
            @click="addCustomerModal = true"
            >Add New Customer</b-btn
          >
        </div>
        <div v-if="!hidePaymentArea">
          <h4 class="mt-2 mb-3">
            Choose Payment Method
          </h4>

          <div
            v-if="
              storeModules.cashOrders &&
                (storeModuleSettings.cashAllowedForCustomer ||
                  $route.params.storeView)
            "
          >
            <b-form-checkbox
              v-model="cashOrder"
              class="pb-2 mediumCheckbox mt-1 mb-1"
            >
              {{ storeModuleSettings.cashOrderWording }}
            </b-form-checkbox>
            <!-- <p
              v-if="
                storeModuleSettings.cashAllowedForCustomer && cashOrder && creditCardList.length === 0 && creditCardId === null
              "
            >
              Please add a credit card on file in order to proceed with a cash
              order. In the event that cash is not paid, your credit card will be
              charged.
            </p> -->
          </div>

          <card-picker
            v-if="!cashOrder"
            :selectable="true"
            :creditCards="creditCardList"
            v-model="card"
            class="mb-3"
            ref="cardPicker"
            :gateway="gateway"
          ></card-picker>

          <b-form-group
            v-if="$route.params.storeView && storeModules.deposits"
            horizontal
            label="Deposit %"
          >
            <b-form-input
              v-model="deposit"
              type="text"
              required
              placeholder="Deposit %"
            ></b-form-input>
          </b-form-group>
        </div>

        <div
          v-if="hasActiveSubscription"
          class="alert alert-warning"
          role="alert"
        >
          You already have an active subscription with this company. Are you
          sure you want to create another subscription instead of adjusting the
          original one?
        </div>

        <b-btn
          v-if="
            // Condense all this logic / put in computed prop
            (card != null || cashOrder) &&
              (minimumMet || $route.params.storeView) &&
              $route.params.adjustOrder != true &&
              $route.params.subscriptionId === undefined &&
              (store.settings.open === true || $route.params.storeView) &&
              (willDeliver || pickup === 1 || $route.params.storeView) &&
              (customer != null || !$route.params.storeView)
          "
          @click="checkout"
          :disabled="checkingOut"
          class="menu-bag-btn mb-4"
          >CHECKOUT</b-btn
        >

        <b-btn
          v-if="$route.params.adjustOrder"
          @click="adjust"
          class="menu-bag-btn"
          >ADJUST ORDER</b-btn
        >
        <b-alert v-if="$route.params.adjustOrder" variant="warning" show>
          <p class="center-text mt-3">
            Any adjustment to price only changes the numbers in the system for
            your records. It does not charge or refund the customer the
            difference.
          </p>
        </b-alert>

        <div v-if="subscriptionId" class="d-none d-lg-block">
          <b-btn
            class="menu-bag-btn update-meals-btn"
            @click="updateSubscriptionMeals"
            >UPDATE MEALS</b-btn
          >
        </div>

        <div
          v-if="
            !willDeliver &&
              $route.params.manualOrder === null &&
              pickup != 1 &&
              $parent.orderId === undefined
          "
        >
          <b-alert v-if="!loading" variant="danger center-text" show
            >You are outside of the delivery area.</b-alert
          >
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

    <li class="transfer-instruction mt-2" v-if="!$route.params.storeView">
      <p class="strong">{{ transferText }}</p>
      <p v-html="transferInstructions"></p>
    </li>

    <li
      v-if="
        minOption === 'meals' &&
          total < minimumMeals &&
          !$route.params.storeView
      "
    >
      <p class="strong">
        Please add {{ remainingMeals }} {{ singOrPlural }} to continue.`
      </p>
    </li>

    <li
      v-if="
        minOption === 'price' &&
          totalBagPricePreFees < minPrice &&
          !$route.params.storeView
      "
    >
      <p class="strong">
        Please add
        {{ format.money(remainingPrice, storeSettings.currency) }}
        more to continue.
      </p>
    </li>

    <add-customer-modal
      :addCustomerModal="addCustomerModal"
    ></add-customer-modal>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";
import SalesTax from "sales-tax";
import CardPicker from "../../components/Billing/CardPicker";
import { createToken } from "vue-stripe-elements-plus";
import AddCustomerModal from "../../components/Customer/AddCustomerModal";

export default {
  components: {
    CardPicker,
    AddCustomerModal
  },
  data() {
    return {
      stripeKey: window.app.stripe_key,
      loading: false,
      checkingOut: false,
      deposit: 100,
      creditCardId: null,
      couponCode: "",
      addCustomerModal: false,
      weeklySubscriptionValue: null
    };
  },
  props: {
    preview: false,
    manualOrder: false,
    forceValue: false,
    cashOrder: false,
    mobile: false,
    salesTax: 0,
    creditCardList: null,
    customer: null,
    orderId: null,
    deliveryDay: null,
    transferTime: null,
    pickup: 0,
    orderLineItems: [],
    checkoutData: null,
    gateway: {
      type: String,
      default: "stripe"
    }
  },
  mounted: function() {
    if (this.forceValue) {
      if (this.customer) {
        this.getCards();
      }
    }
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
      loggedIn: "loggedIn",
      subscriptions: "subscriptions"
    }),
    storeSettings() {
      return this.store.settings;
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

      let grouped = [];
      customers.forEach(customer => {
        grouped.push({
          value: customer.id,
          text: customer.name
        });
      });

      let sorted = grouped.sort((a, b) => a.text.localeCompare(b.text));

      return sorted;
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
    deliveryInstructions() {
      if (this.storeSettings.deliveryInstructions != null) {
        return this.storeSettings.deliveryInstructions.replace(/\n/g, "<br>");
      } else return;
      this.storeSettings.deliveryInstructions;
    },
    pickupInstructions() {
      if (this.storeSettings.pickupInstructions != null) {
        return this.storeSettings.pickupInstructions.replace(/\n/g, "<br>");
      } else return;
      this.storeSettings.pickupInstructions;
    },
    transferInstructions() {
      if (this.pickup === 0) return this.deliveryInstructions;
      else if (this.pickup === 1) return this.pickupInstructions;
    },
    pickupLocationOptions() {
      return this.pickupLocations.map(loc => {
        return {
          value: loc.id,
          text: loc.name
        };
      });
    },
    cards() {
      if (this.$route.params.manualOrder) {
        return this.creditCardList;
      }
      if (this.creditCard != null) return [this.creditCard];
      else return this.creditCards;
    },
    card() {
      if (this.creditCardId != null) {
        return this.creditCardId;
      }

      if (this.creditCards.length != 1) return null;
      else return this.creditCards[0].id;
    },
    transferTimeOptions() {
      let startTime = null;
      let endTime = null;
      if (this.pickup === 1) {
        startTime = parseInt(
          this.storeModuleSettings.pickupStartTime.substr(0, 2)
        );
        endTime = parseInt(this.storeModuleSettings.pickupEndTime.substr(0, 2));
      } else {
        startTime = parseInt(
          this.storeModuleSettings.deliveryStartTime.substr(0, 2)
        );
        endTime = parseInt(
          this.storeModuleSettings.deliveryEndTime.substr(0, 2)
        );
      }

      let hourOptions = [];

      while (startTime <= endTime) {
        hourOptions.push(startTime);
        startTime++;
      }

      let transferTimeRange = this.storeModuleSettings.transferTimeRange;
      let newHourOptions = [];

      hourOptions.forEach(option => {
        if (option < 12) {
          option = option.toString();
          let period = " AM";
          if (parseInt(option) === 11) {
            period = " PM";
          }
          let hour = 1;
          let newOption = option.concat(" AM");
          if (transferTimeRange) {
            newOption.concat(" - " + (parseInt(option) + hour) + period);
            let finalOption = newOption.concat(
              " - " + (parseInt(option) + hour) + period
            );
            newHourOptions.push(finalOption);
          } else newHourOptions.push(newOption);
        } else {
          if (option > 12) {
            option = option - 12;
          }
          let hour = 1;
          let period = " PM";
          if (parseInt(option) === 11) {
            period = " AM";
          }
          if (parseInt(option) === 12) {
            hour = -11;
          }
          option = option.toString();
          let newOption = option.concat(" PM");
          if (transferTimeRange) {
            newOption.concat(" - " + (parseInt(option) + hour) + period);
            let finalOption = newOption.concat(
              " - " + (parseInt(option) + hour) + period
            );
            newHourOptions.push(finalOption);
          } else newHourOptions.push(newOption);
        }
      });

      return newHourOptions;
    },
    transferType() {
      return this.storeSettings.transferType.split(",");
    },
    transferText() {
      if (this.pickup === 0 && this.deliveryInstructions != null)
        return "Delivery Instructions";
      else if (this.pickup === 1 && this.pickupInstructions != null)
        return "Pickup Instructions";
    },
    transferTypeCheckDelivery() {
      if (_.includes(this.transferType, "delivery")) return true;
    },
    transferTypeCheckPickup() {
      if (_.includes(this.transferType, "pickup")) return true;
    },
    deliveryDaysOptions() {
      let options = [];
      let dates = this.storeSettings.next_orderable_delivery_dates;
      if (this.storeModules.ignoreCutoff && this.$route.params.storeView)
        dates = this.storeSettings.next_delivery_dates;

      dates.forEach(date => {
        options.push({
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        });
      });

      return options;
    },
    deliveryDaysOptionsStoreView() {
      let options = [];
      var today = new Date();

      var year = today.getFullYear();
      var month = today.getMonth();
      var date = today.getDate();

      for (var i = 0; i < 30; i++) {
        var day = new Date(year, month, date + i);
        options.push({
          value: moment(day).format("YYYY-MM-DD 00:00:00"),
          text: moment(day).format("dddd MMM Do")
        });
      }
      return options;
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
      if (this.orderLineItems != null) {
        this.orderLineItems.forEach(orderLineItem => {
          totalLineItemsPrice += orderLineItem.price * orderLineItem.quantity;
        });
      }
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
      if (this.applyMealPlanDiscount && this.weeklySubscription) {
        return this.afterCoupon - this.mealPlanDiscount;
      } else return this.afterCoupon;
    },
    deliveryFeeAmount() {
      if (!this.pickup) {
        if (!this.couponFreeDelivery) {
          if (this.storeSettings.applyDeliveryFee) {
            if (this.storeSettings.deliveryFeeType === "flat") {
              // DBD Temp Workaround. Remove when adding the double delivery day feature.
              let addedFee = this.DBD();
              //
              return this.storeSettings.deliveryFee + addedFee;
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
      return this.afterFees + this.tax;
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
    weeklySubscription() {
      if (
        this.checkoutData &&
        this.checkoutData.hasOwnProperty("weeklySubscriptionValue")
      ) {
        return this.checkoutData.weeklySubscriptionValue;
      }

      if (this.$route.params.subscriptionId != null) {
        return true;
      } else {
        return this.deliveryPlan;
      }
    },
    deliveryPlanText() {
      if (this.weeklySubscription) return "Prepared Weekly";
      else return "Prepared Once";
    },
    tax() {
      if (this.storeSettings.enableSalesTax === 0) {
        return 0;
      }
      if (this.storeSettings.salesTax > 0)
        return (this.storeSettings.salesTax / 100) * this.afterDiscount;
      else return this.salesTax * this.afterDiscount;
    },
    subscriptionId() {
      return this.$route.params.subscriptionId;
    },
    minimumMet() {
      if (
        (this.minOption === "meals" && this.total >= this.minimumMeals) ||
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
    hidePaymentArea() {
      let params = this.$route.params;
      if (
        params.subscriptionId != null ||
        params.preview != null ||
        params.adjustOrder
      )
        return true;
      else return false;
    },
    hasActiveSubscription() {
      let hasActiveSub = false;
      if (this.subscriptions) {
        this.subscriptions.forEach(subscription => {
          if (
            subscription.store_id === this.store.id &&
            subscription.status === "active" &&
            this.weeklySubscription
          )
            hasActiveSub = true;
        });
      }
      return hasActiveSub;
    }
  },
  methods: {
    ...mapActions([
      "refreshSubscriptions",
      "refreshStoreSubscriptions",
      "refreshCustomerOrders",
      "refreshOrders",
      "refreshOrdersToday",
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
      if (this.$route.params.storeView) {
        return true;
      }
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
    changeDeliveryDay() {
      this.updateParentData();
    },
    changePickupV() {
      this.updateParentData();
    },
    setWeeklySubscriptionValue(v) {
      this.weeklySubscriptionValue = v;
    },
    updateParentData() {
      this.$emit("updateData", {
        customer: this.customer,
        weeklySubscriptionValue: this.weeklySubscriptionValue,
        pickup: this.pickup,
        transferTime: this.transferTime,
        deliveryDay: this.deliveryDay,
        cashOrder: this.cashOrder
      });
    },
    getCards() {
      this.updateParentData();

      window.localStorage.clear();
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
    async adjust() {
      let deposit = this.deposit;
      if (deposit.toString().includes("%")) {
        deposit.replace("%", "");
        deposit = parseInt(deposit);
      }

      let weeklySubscriptionValue = this.storeSettings.allowMealPlans
        ? this.weeklySubscriptionValue
        : 0;

      axios
        .post(`/api/me/orders/adjustOrder`, {
          orderId: this.$parent.orderId,
          deliveryDate: this.deliveryDay,
          pickup: this.pickup,
          transferTime: this.transferTime,
          subtotal: this.subtotal,
          afterDiscount: this.afterDiscount,
          deliveryFee: this.deliveryFeeAmount,
          processingFee: this.processingFeeAmount,
          bag: this.bag,
          plan: weeklySubscriptionValue,
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
          lineItemsOrder: this.orderLineItems,
          grandTotal: this.grandTotal
        })
        .then(resp => {
          this.$toastr.s("Order Adjusted");
          this.$router.push({ path: "/store/orders" });
          this.refreshUpcomingOrders();
        });
    },
    mounted() {
      this.creditCardId = this.card;

      if (!_.includes(this.transferType, "delivery")) this.pickup = 1;

      this.selectedPickupLocation = this.pickupLocationOptions[0].value;
    },
    updated() {
      this.creditCardId = this.card;

      this.$eventBus.$on("chooseCustomer", () => {
        this.chooseCustomer();
      });
    },
    checkout() {
      if (this.grandTotal <= 0 && !this.cashOrder) {
        this.$toastr.e(
          "At least .50 cents is required to process an order.",
          "Error"
        );
        return;
      }

      if (this.checkingOut) {
        return;
      }

      this.checkingOut = true;

      this.deliveryFee = this.deliveryFeeAmount;
      if (this.pickup === 0) {
        this.selectedPickupLocation = null;
      }

      if (!this.deliveryDay && this.deliveryDaysOptions) {
        this.deliveryDay = this.deliveryDaysOptions[0].value;
      }

      let deposit = this.deposit;
      if (deposit.toString().includes("%")) {
        deposit.replace("%", "");
        deposit = parseInt(deposit);
      }

      let endPoint = "";
      if (this.$route.params.manualOrder) {
        endPoint = "/api/me/checkout";
      } else {
        endPoint = "/api/bag/checkout";
      }

      let cardId = this.card;

      if (this.cashOrder === true) {
        cardId = 0;
      }

      let weeklySubscriptionValue = this.storeSettings.allowMealPlans
        ? this.weeklySubscriptionValue
        : 0;

      if (this.cashOrder === null) {
        this.cashOrder = 0;
      }

      axios
        .post(endPoint, {
          subtotal: this.subtotal,
          afterDiscount: this.afterDiscount,
          bag: this.bag,
          plan: weeklySubscriptionValue,
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
          let weeklyDelivery = this.weeklySubscription;
          this.setBagMealPlan(false);
          this.setBagCoupon(null);

          if (this.$route.params.manualOrder && weeklyDelivery) {
            this.refreshStoreSubscriptions();
            this.$router.push({
              path: "/store/subscriptions"
            });
            return;
          } else if (this.$route.params.manualOrder && !weeklyDelivery) {
            this.refreshUpcomingOrders();
            this.refreshOrdersToday();
            this.refreshOrders();
            this.$router.push({
              path: "/store/orders"
            });
            return;
          }
          if (weeklyDelivery) {
            await this.refreshSubscriptions();
            this.$router.push({
              path: "/customer/subscriptions",
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
        .catch(async response => {
          let error =
            "Checkout failed. Reason: " +
            response.response.data.message +
            ". Please contact GoPrep";
          this.$toastr.e(error, "Error");
        })
        .finally(() => {
          this.loading = false;
        });
    },
    setCustomer() {
      this.customer = this.storeCustomers.slice(-1)[0].id;
    },
    removeCoupon() {
      this.coupon = {};
      this.setBagCoupon(null);
      this.couponCode = "";
    },

    // Temporary work around for two delivery fees based on day of the week. Will remove when two delivery day feature is added.
    DBD() {
      if (this.store.id === 100) {
        let cat = [];
        this.bag.forEach(item => {
          if (!cat.includes(item.meal.category_ids[0]))
            cat.push(item.meal.category_ids[0]);
        });
        if (cat.length > 1) return 5;
        else return 0;
      } else return 0;
    }
  }
};
</script>
