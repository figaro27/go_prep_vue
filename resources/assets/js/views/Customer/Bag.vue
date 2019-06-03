<template>
  <div class="bag">
    <div class="card">
      <div class="card-body">
        <spinner v-if="loading" position="absolute"></spinner>
        <div class="row">
          <div class="col-sm-12 store-logo-area" v-if="!mobile">
            <a :href="storeWebsite" v-if="storeWebsite != null">
              <img
                v-if="storeLogo"
                class="store-logo"
                :src="storeLogo"
                alt="Company Logo"
              />
            </a>
            <img
              v-if="storeLogo && storeWebsite === null"
              class="store-logo"
              :src="storeLogo"
              alt="Company Logo"
            />
          </div>
          <div class="col-md-12 mb-2 bag-actions">
            <b-button
              size="lg"
              class="brand-color white-text"
              to="/customer/menu"
            >
              <span class="d-sm-inline">Change Meals</span>
            </b-button>
            <b-button size="lg" class="gray white-text" @click="clearAll">
              <span class="d-sm-inline">Empty Bag</span>
            </b-button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-5">
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
                      <li
                        v-for="component in itemComponents(item)"
                        class="plain"
                      >
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
            <p class="mt-3" v-if="minOption === 'meals' && total < minMeals">
              Please add {{ remainingMeals }} {{ singOrPlural }} to continue.
            </p>
            <router-link to="/customer/menu">
              <b-btn
                v-if="minOption === 'meals' && total < minMeals && !preview"
                class="menu-bag-btn mb-2"
                >BACK</b-btn
              >
            </router-link>

            <p
              class="mt-3"
              v-if="minOption === 'price' && totalBagPrice < minPrice"
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
                      totalBagPrice <= minPrice &&
                      !preview
                  "
                  class="menu-bag-btn"
                  >BACK</b-btn
                >
              </router-link>
            </div>
          </div>
          <div class="col-md-6 offset-md-1">
            <ul class="list-group">
              <li class="bag-item">
                <div class="row">
                  <div class="col-md-8 pb-1">
                    <h3>
                      <strong
                        >Weekly Meal Plan
                        <span v-if="storeSettings.applyMealPlanDiscount"
                          >{{ storeSettings.mealPlanDiscount }}% Off</span
                        ></strong
                      >
                      <img
                        v-b-popover.hover="
                          'Choose a weekly meal plan instead of a one time order and meals will be given to you on a weekly basis. You can swap out meals as well as pause or cancel the meal plan at any time. This will apply to the following week\'s renewal.'
                        "
                        title="Weekly Meal Plan"
                        src="/images/store/popover.png"
                        class="popover-size ml-1"
                      />
                    </h3>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <strong
                      ><p
                        class="mr-1"
                        v-if="storeSettings.applyMealPlanDiscount"
                      >
                        Create a meal plan and you'll save
                        <span class="text-success standout">{{
                          format.money(mealPlanDiscount, storeSettings.currency)
                        }}</span>
                        on each order.
                        <c-switch
                          color="success"
                          variant="pill"
                          size="lg"
                          v-model="deliveryPlan"
                          class="pt-3"
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
                    <span class="text-success"
                      >({{
                        format.money(couponReduction, storeSettings.currency)
                      }})</span
                    >
                  </div>
                </div>
              </li>
              <li
                class="checkout-item"
                v-if="deliveryPlan && applyMealPlanDiscount"
              >
                <div class="row">
                  <div class="col-6 col-md-4">
                    <strong>Meal Plan Discount:</strong>
                  </div>
                  <div class="col-6 col-md-3 offset-md-5 text-success">
                    ({{
                      format.money(mealPlanDiscount, storeSettings.currency)
                    }})
                  </div>
                </div>
              </li>
              <li
                class="checkout-item"
                v-if="storeSettings.applyDeliveryFee && pickup === 0"
              >
                <div class="row">
                  <div class="col-6 col-md-4">
                    <strong>Delivery Fee:</strong>
                  </div>
                  <div class="col-6 col-md-3 offset-md-5">
                    {{
                      format.money(deliveryFeeAmount, storeSettings.currency)
                    }}
                  </div>
                </div>
              </li>
              <li class="checkout-item" v-if="storeSettings.applyProcessingFee">
                <div class="row">
                  <div class="col-6 col-md-4">
                    <strong>Processing Fee:</strong>
                  </div>
                  <div class="col-6 col-md-3 offset-md-5">
                    {{
                      format.money(
                        storeSettings.processingFee,
                        storeSettings.currency
                      )
                    }}
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

              <li :class="couponClass" v-if="hasCoupons">
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
                  <p
                    v-if="
                      pickup === 0 &&
                        transferTypeCheck !== 'pickup' &&
                        deliveryDaysOptions.length > 1
                    "
                  >
                    Delivery Day
                  </p>
                  <p v-if="pickup === 1 && deliveryDaysOptions.length > 1">
                    Pickup Day
                  </p>
                  <b-form-group
                    v-if="deliveryDaysOptions.length > 1"
                    description
                  >
                    <b-select
                      :options="deliveryDaysOptions"
                      v-model="deliveryDay"
                      @input="val => (deliveryDay = val)"
                      class="delivery-select"
                      required
                    >
                      <option slot="top" disabled
                        >-- Select delivery day --</option
                      >
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
                  <div v-if="storeSettings.hasPickupLocations && pickup === 1">
                    <p>Pickup Location</p>
                    <b-select
                      v-model="selectedPickupLocation"
                      :options="pickupLocationOptions"
                      class="delivery-select mb-3"
                      required
                    ></b-select>
                  </div>
                </div>
              </li>

              <li
                class="checkout-item"
                v-if="
                  minOption === 'meals' && total < minimumMeals && !manualOrder
                "
              >
                <p>
                  Please add {{ remainingMeals }} {{ singOrPlural }} to
                  continue.`
                </p>
              </li>

              <li
                class="checkout-item"
                v-if="
                  minOption === 'price' &&
                    totalBagPrice < minPrice &&
                    !manualOrder
                "
              >
                <p>
                  Please add
                  {{ format.money(remainingPrice, storeSettings.currency) }}
                  more to continue.
                </p>
              </li>

              <li v-else-if="loggedIn">
                <div v-if="!willDeliver && pickup != 1">
                  <b-alert v-if="!loading" variant="danger center-text" show
                    >You are outside of the delivery area.</b-alert
                  >
                </div>
                <div v-else>
                  <h4 class="mt-2 mb-3">Choose Payment Method</h4>
                  <card-picker :selectable="true" v-model="card"></card-picker>
                  <b-btn
                    v-if="card && minOption === 'meals' && total >= minMeals"
                    @click="checkout"
                    class="menu-bag-btn"
                    >CHECKOUT</b-btn
                  >
                  <b-btn
                    v-if="
                      card != null &&
                        minOption === 'price' &&
                        totalBagPrice >= minPrice &&
                        storeSettings.open
                    "
                    @click="checkout"
                    class="menu-bag-btn"
                    >CHECKOUT</b-btn
                  >
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
            </ul>

            <li
              class="transfer-instruction mt-2"
              v-if="
                transferTypeCheckDelivery &&
                  pickup === 0 &&
                  storeSettings.deliveryInstructions
              "
            >
              <p class="strong">Delivery Instructions:</p>
              <p v-html="deliveryInstructions"></p>
            </li>
            <li
              class="transfer-instruction mt-2"
              v-if="
                transferTypeCheckPickup &&
                  pickup === 1 &&
                  storeSettings.pickupInstructions
              "
            >
              <p class="strong">Pickup Instructions:</p>
              <p v-html="pickupInstructions">{{ pickupInstructions }}</p>
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
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
// import { stripeKey, stripeOptions } from "../../config/stripe.json";
import { createToken } from "vue-stripe-elements-plus";
import SalesTax from "sales-tax";

import MenuBag from "../../mixins/menuBag";
import CardPicker from "../../components/Billing/CardPicker";

export default {
  components: {
    cSwitch,
    CardPicker,
    SalesTax
  },
  mixins: [MenuBag],
  data() {
    return {
      selectedPickupLocation: null,
      pickup: 0,
      deliveryPlan: false,
      deliveryDay: undefined,
      stripeKey: window.app.stripe_key,
      // stripeOptions,
      loading: false,
      checkingOut: false,
      salesTax: 0,
      coupon: {},
      couponCode: "",
      couponApplied: false,
      couponClass: "checkout-item",
      deliveryFee: 0
    };
  },
  computed: {
    ...mapGetters({
      cards: "cards",
      store: "viewedStore",
      storeSetting: "viewedStoreSetting",
      total: "bagQuantity",
      bag: "bagItems",
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
      getMeal: "viewedStoreMeal"
    }),
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
    deliveryInstructions() {
      return this.storeSettings.deliveryInstructions.replace(/\n/g, "<br>");
    },
    pickupInstructions() {
      return this.storeSettings.pickupInstructions.replace(/\n/g, "<br>");
    },
    pickupLocationOptions() {
      return this.pickupLocations.map(loc => {
        return {
          value: loc.id,
          text: loc.name
        };
      });
    },
    deliveryFeeAmount() {
      if (this.storeSettings.applyDeliveryFee) {
        if (this.storeSettings.deliveryFeeType === "flat") {
          return this.storeSettings.deliveryFee;
        } else if (this.storeSettings.deliveryFeeType === "mileage") {
          let mileageBase = parseFloat(this.storeSettings.mileageBase);
          let mileagePerMile = parseFloat(this.storeSettings.mileagePerMile);
          let distance = parseFloat(this.store.distance);
          return mileageBase + mileagePerMile * distance;
        }
      } else return 0;
    },
    card() {
      if (this.cards.length != 1) return null;
      else return this.cards[0].id;
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
      let subtotal = this.totalBagPricePreFees;
      return subtotal;
    },
    couponReduction() {
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
    afterFees() {
      let applyDeliveryFee = this.storeSettings.applyDeliveryFee;
      let applyProcessingFee = this.storeSettings.applyProcessingFee;
      let deliveryFee = this.deliveryFeeAmount;
      let processingFee = this.storeSettings.processingFee;
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
      return this.storeSetting("next_orderable_delivery_dates", []).map(
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
    }
  },
  mounted() {
    this.deliveryDay = this.deliveryDaysOptions[0].value;
    this.getSalesTax(this.store.details.state);

    if (!_.includes(this.transferType, "delivery")) this.pickup = 1;

    this.selectedPickupLocation = this.pickupLocationOptions[0].value;
  },
  methods: {
    ...mapActions(["refreshSubscriptions", "refreshCustomerOrders"]),
    ...mapMutations(["emptyBag"]),
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    checkout() {
      if (this.checkingOut) {
        return;
      }

      // this.loading = true;
      this.checkingOut = true;

      this.deliveryFee = this.deliveryFeeAmount;
      if (this.pickup === 0) {
        this.selectedPickupLocation = null;
      }
      axios
        .post("/api/bag/checkout", {
          subtotal: this.afterDiscount,
          bag: this.bag,
          plan: this.deliveryPlan,
          pickup: this.pickup,
          delivery_day: this.deliveryDay,
          card_id: this.card,
          store_id: this.store.id,
          salesTax: this.tax,
          coupon_id: this.coupon.id,
          couponReduction: this.couponReduction,
          couponCode: this.coupon.code,
          deliveryFee: this.deliveryFee,
          pickupLocation: this.selectedPickupLocation
        })
        .then(async resp => {
          this.emptyBag();

          if (this.deliveryPlan) {
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
          this.coupon = coupon;
          this.couponApplied = true;
          this.couponCode = "";
          this.couponClass = "checkout-item-hide";
          this.$toastr.s("Coupon Applied.", "Success");
          return;
        }
      });
    }
  }
};
</script>
