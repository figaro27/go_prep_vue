<template>
  <div>
    <ul class="list-group" v-if="$parent.orderId === undefined">
      <li
        class="bag-item"
        v-if="
          storeSettings.allowMealPlans &&
            $route.params.subscriptionId === undefined
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
                >Subscribe & Save
                <span v-if="storeSettings.applyMealPlanDiscount"
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
                <span v-if="storeSettings.applyMealPlanDiscount">
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

    <li
      class="checkout-item unset-height"
      v-if="
        deliveryDaysOptions.length > 1 &&
          $route.params.subscriptionId === undefined
      "
    >
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
    </li>
    <li
      class="checkout-item"
      v-if="
        deliveryDaysOptions.length === 1 &&
          $route.params.subscriptionId === undefined
      "
    >
      <div>
        <h6 v-if="pickup === 0">
          Delivery Day: {{ deliveryDaysOptions[0].text }}
        </h6>
        <h6 v-if="pickup === 1">
          Pickup Day: {{ deliveryDaysOptions[0].text }}
        </h6>
      </div>
    </li>

    <li
      class="checkout-item unset-height"
      v-if="
        $parent.orderId === undefined &&
          storeModules.pickupLocations &&
          pickup &&
          $route.params.subscriptionId === undefined
      "
    >
      <div>
        <p>Pickup Location</p>
        <b-select
          v-model="selectedPickupLocation"
          :options="pickupLocationOptions"
          class="delivery-select mb-3"
          required
        ></b-select>
      </div>
    </li>

    <li
      class="checkout-item"
      v-if="
        storeModules.transferHours &&
          pickup &&
          $route.params.subscriptionId === undefined
      "
    >
      <div>
        <strong>Pickup Time</strong>
        <b-form-select
          class="ml-2"
          v-model="transferTime"
          :options="transferTimeOptions"
        ></b-form-select>
      </div>
    </li>

    <li v-if="loggedIn">
      <div v-if="!willDeliver && !manualOrder && pickup != 1">
        <b-alert v-if="!loading" variant="danger center-text" show
          >You are outside of the delivery area.</b-alert
        >
      </div>
      <div>
        <div v-if="$route.params.manualOrder">
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
            <b-form-checkbox v-model="cashOrder" class="pb-2 mediumCheckbox">
              Cash
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
          ></card-picker>

          <b-form-group
            v-if="
              $route.params.storeView && storeModules.deposits && !cashOrder
            "
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

        <b-btn
          v-if="card != null && minimumMet && $route.params.adjustOrder != true"
          @click="checkout"
          class="menu-bag-btn"
          >CHECKOUT</b-btn
        >

        <b-btn
          v-if="$route.params.adjustOrder"
          @click="adjust"
          class="menu-bag-btn"
          >ADJUST ORDER</b-btn
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

    <li class="transfer-instruction mt-2" v-if="$parent.orderId === undefined">
      <p class="strong">{{ transferText }}</p>
      <p v-html="transferInstructions"></p>
    </li>

    <li v-if="minOption === 'meals' && total < minimumMeals && !manualOrder">
      <p class="strong">
        Please add {{ remainingMeals }} {{ singOrPlural }} to continue.`
      </p>
    </li>

    <li
      v-if="
        minOption === 'price' && totalBagPricePreFees < minPrice && !manualOrder
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
      addCustomerModal: false
    };
  },
  props: {
    preview: false,
    manualOrder: false,
    cashOrder: false,
    mobile: false,
    salesTax: 0,
    creditCardList: null,
    customer: null,
    orderId: null,
    deliveryDay: null,
    transferTime: null,
    pickup: {
      default: 0
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
      loggedIn: "loggedIn"
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
      if (this.storeSettings.pickupInstruction != null) {
        return this.storeSettings.deliveryInstructions.replace(/\n/g, "<br>");
      } else return;
      this.storeSettings.deliveryInstructions;
    },
    pickupInstructions() {
      if (this.storeSettings.pickupInstruction != null) {
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
      if (this.$route.params.storeView) {
        return 0;
      }

      if (this.creditCardId != null) {
        return this.creditCardId;
      }

      if (this.creditCards.length != 1) return null;
      else return this.creditCards[0].id;
    },
    transferTimeOptions() {
      let startTime = parseInt(
        this.storeModuleSettings.transferStartTime.substr(0, 2)
      );
      let endTime = parseInt(
        this.storeModuleSettings.transferEndTime.substr(0, 2)
      );
      let hourOptions = [];

      while (startTime <= endTime) {
        hourOptions.push(startTime);
        startTime++;
      }

      let newHourOptions = [];
      hourOptions.forEach(option => {
        if (option < 12) {
          option = option.toString();
          let newOption = option.concat(" ", "AM");
          newHourOptions.push(newOption);
        } else {
          if (option > 12) {
            option = option - 12;
          }
          option = option.toString();
          let newOption = option.concat(" ", "PM");
          newHourOptions.push(newOption);
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
      this.storeSettings.next_orderable_delivery_dates.forEach(date => {
        options.push({
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        });
      });

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
      let subtotal = this.totalBagPricePreFees;
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
      if (this.$route.params.subscriptionId != null) return true;
      else return this.deliveryPlan;
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
        return (this.storeSettings.salesTax / 100) * this.afterFees;
      else return this.salesTax * this.afterFees;
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
    async adjust() {
      axios
        .post(`/api/me/orders/adjustOrder`, {
          bag: this.bag,
          orderId: this.$parent.orderId,
          deliveryDate: this.deliveryDay,
          pickup: this.pickup,
          transferTime: this.transferTime
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
      axios
        .post(endPoint, {
          subtotal: this.subtotal,
          afterDiscount: this.afterDiscount,
          bag: this.bag,
          plan: this.weeklySubscription,
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
    setCustomer() {
      this.customer = Object.keys(this.customers)[
        Object.keys(this.customers).length - 1
      ];
    }
  }
};
</script>
