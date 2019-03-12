<template>
  <div class="bag">
    <div class="card">
      <div class="card-body">
        <spinner v-if="loading" position="absolute"></spinner>
        <div class="row">
          <div class="col-md-12">
            <h2 class="center-text dbl-underline">Checkout</h2>
          </div>
          <div class="col-md-12 mb-2 bag-actions">
            <b-button size="lg" class="brand-color white-text" to="/customer/menu"><span class="d-none d-sm-inline">Change Meals</span></b-button>
            <b-button size="lg" class="gray white-text" @click="clearAll"><span class="d-none d-sm-inline">Empty Bag</span></b-button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-5">
            <ul class="list-group">
              <li v-for="(item, mealId) in bag" :key="`bag-${mealId}`" class="bag-item">
                <div v-if="item && item.quantity > 0" class="d-flex align-items-center">
                  <div class="bag-item-quantity mr-2">
                    <div @click="addOne(item.meal)" class="bag-plus-minus brand-color white-text"><i>+</i></div>
                    <p class="bag-quantity">{{ item.quantity }}</p>
                    <div @click="minusOne(item.meal)" class="bag-plus-minus gray white-text"><i>-</i></div>
                  </div>
                  <div class="bag-item-image mr-2">
                    <thumbnail
                      :src="item.meal.featured_image"
                      :spinner="false"
                      class="cart-item-img"
                    ></thumbnail>
                  </div>
                  <div class="flex-grow-1 mr-2">{{ item.meal.title }}</div>
                  <div class="flex-grow-0">
                    <img
                      src="/images/customer/x.png"
                      @click="clearMeal(item.meal)"
                      class="clear-meal"
                    >
                  </div>
                </div>
              </li>
            </ul>
            <p
              class="mt-3"
              v-if="minOption === 'meals' && total < minMeals"
            >Please add {{ remainingMeals }} {{ singOrPlural }} to continue.</p>
            <router-link to="/customer/menu">
              <b-btn
                v-if="minOption === 'meals' && total < minMeals && !preview"
                class="menu-bag-btn mb-2"
              >BACK</b-btn>
            </router-link>

            <p
              class="mt-3"
              v-if="minOption === 'price' && totalBagPrice < minPrice"
            >Please add {{format.money(remainingPrice)}} more to continue.</p>
            <div>
              <router-link to="/customer/menu">
                <b-btn
                  v-if="minOption === 'price' && totalBagPrice <= minPrice && !preview"
                  class="menu-bag-btn"
                >BACK</b-btn>
              </router-link>
            </div>
          </div>
          <div class="col-md-6 offset-md-1">
            <ul class="list-group">
              <li class="bag-item">
                <div class="row">
                  <div class="col-md-3">
                    <p>
                      <strong>Weekly Meal Plan</strong>
                      <img
                        v-b-popover.hover="'Choose a weekly meal plan instead of a one time order and meals will be given to you on a weekly basis. You can swap out meals as well as pause or cancel the meal plan if it is within a certain amount of time before the pickup/delivery day.'"
                        title="Weekly Meal Plan"
                        src="/images/store/popover.png"
                        class="popover-size ml-1"
                      >
                    </p>
                  </div>
                  <div class="col-md-4">
                    <div class="aside-options">
                      <c-switch color="success" variant="pill" size="lg" v-model="deliveryPlan"/>
                    </div>
                  </div>
                </div>
              </li>
              <li class="checkout-item">
                <p>
                  <strong>{{ total }} {{ singOrPluralTotal }} {{ deliveryPlanText }}</strong>
                </p>
              </li>
              <li
                class="checkout-item"
                
              >
                <div class="row">
                  <div class="col-md-4">
                    <strong>Subtotal:</strong>
                  </div>
                  <div class="col-md-3 offset-5">{{ format.money(preFeePreDiscount) }}</div>
                </div>
              </li>
              <li class="checkout-item" v-if="deliveryPlan && applyMealPlanDiscount">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Meal Plan Discount:</strong>
                  </div>
                  <div class="col-md-3 offset-5 red">({{ format.money(mealPlanDiscount) }})</div>
                </div>
              </li>
              <li class="checkout-item" v-if="storeSettings.applyDeliveryFee">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Delivery Fee:</strong>
                  </div>
                  <div class="col-md-3 offset-5">{{ format.money(storeSettings.deliveryFee) }}</div>
                </div>
              </li>
              <li class="checkout-item" v-if="storeSettings.applyProcessingFee">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Processing Fee:</strong>
                  </div>
                  <div class="col-md-3 offset-5">{{ format.money(storeSettings.processingFee) }}</div>
                </div>
              </li>

              <li class="checkout-item">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Sales Tax:</strong>
                  </div>
                  <div class="col-md-3 offset-5">{{ format.money(tax) }}</div>
                </div>
              </li>

              <li class="checkout-item">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Total</strong>
                  </div>
                  <div class="col-md-3 offset-5">
                    <strong>{{ format.money(afterDiscountAfterFees) }}</strong>
                  </div>
                </div>
              </li>

              <li class="checkout-item" v-if="transferTypeCheck === 'both'">
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
              <li class="checkout-item" v-if="transferTypeCheck === 'both' && pickup === 1">
                <p>
                  <strong>Pickup Instructions:</strong>
                  {{ storeSettings.pickupInstructions }}
                </p>
              </li>

              <li>
                <div>
                  <p v-if="pickup === 0 && transferTypeCheck !== 'pickup' && deliveryDaysOptions.length > 1">Delivery Day</p>
                  <p v-if="pickup === 1 || transferTypeCheck === 'pickup' && deliveryDaysOptions.length > 1" >Pickup Day</p>
                  <b-form-group v-if="deliveryDaysOptions.length > 1" description>
                    <b-select
                      :options="deliveryDaysOptions"
                      v-model="deliveryDay"
                      class="delivery-select"
                      required
                    >
                      <option slot="top" disabled>-- Select delivery day --</option>
                    </b-select>
                  </b-form-group>
                  <div v-else-if="deliveryDaysOptions.length === 1">
                    <p>Delivery Day: {{ deliveryDaysOptions[0].text }}</p>
                  </div>
                </div>
              </li>

              <li class="checkout-item" v-if="minOption === 'meals' && total < minimumMeals && !manualOrder">
                <p>Please add {{ remainingMeals }} {{ singOrPlural }} to continue.`</p>
              </li>

              <li class="checkout-item" v-if="minOption === 'price' && totalBagPrice < minPrice && !manualOrder">
                <p>Please add {{format.money(remainingPrice)}} more to continue.</p>
              </li>

              <li v-else-if="loggedIn">
                <div v-if="!willDeliver && pickup != 1">
                  <b-alert variant="danger center-text" show>You are outside of the delivery area.</b-alert>
                </div>
                <div v-else>
                  <h4 class="mt-2 mb-3">Choose Payment Method</h4>
                  <card-picker :selectable="true" v-model="card"></card-picker>
                  <b-btn
                    v-if="card && minOption === 'meals' && total >= minMeals"
                    @click="checkout"
                    class="menu-bag-btn"
                  >CHECKOUT</b-btn>
                  <b-btn
                    v-if="card && minOption === 'price' && totalBagPrice >= minPrice && storeSettings.open"
                    @click="checkout"
                    class="menu-bag-btn"
                  >CHECKOUT</b-btn>
                </div>
              </li>

              <li v-else>
                <div class="row">
                  <div class="col-md-6">
                    <router-link
                        :to="{ path: '/login', query: { redirect: '/customer/bag' } }"
                      >
                      <b-btn class="menu-bag-btn">
                        LOG IN
                      </b-btn>
                    </router-link>


                  </div>
                  <div class="col-md-6">
                    <router-link :to="{ path: '/register', query: { redirect: '/customer/bag' } }">
                      <b-btn class="menu-bag-btn">
                        REGISTER
                      </b-btn>
                    </router-link>
                  </div>
                </div>
              </li>
            </ul>
            <div v-if="storeSettings.open === false">
              <div class="row">
                <div class="col-sm-12 mt-3">
                  <div class="card">
                    <div class="card-body">
                      <h5 class="center-text">This company will not be taking new orders at this time.</h5>
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
import { stripeKey, stripeOptions } from "../../config/stripe.json";
import { createToken } from "vue-stripe-elements-plus";
import SalesTax from "sales-tax";

import CardPicker from "../../components/Billing/CardPicker";

export default {
  components: {
    cSwitch,
    CardPicker,
    SalesTax
  },
  data() {
    return {
      pickup: 0,
      deliveryPlan: false,
      deliveryDay: undefined,
      stripeKey,
      stripeOptions,
      card: null,
      loading: false,
      salesTax: 0
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeSetting: "viewedStoreSetting",
      total: "bagQuantity",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      totalBagPrice: "totalBagPrice",
      willDeliver: "viewedStoreWillDeliver",
      isLoading: "isLoading",
      storeLogo: "viewedStoreLogo",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice"
    }),
    storeSettings() {
      return this.store.settings;
    },
    transferType() {
      return this.storeSettings.transferType.split(",");
    },
    transferTypeCheck() {
      if (
        _.includes(this.transferType, "delivery") &&
        _.includes(this.transferType, "pickup")
      ) {
        return "both";
      }
      if (
        !_.includes(this.transferType, "delivery") &&
        _.includes(this.transferType, "pickup")
      ) {
        return "pickup";
      }
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
      return this.minPrice - this.totalBagPrice;
    },
    preFeePreDiscount() {
      let applyDeliveryFee = this.storeSettings.applyDeliveryFee;
      let applyProcessingFee = this.storeSettings.applyProcessingFee;
      let deliveryFee = this.storeSettings.deliveryFee;
      let processingFee = this.storeSettings.processingFee;

      if (applyDeliveryFee && applyProcessingFee) {
        return this.totalBagPrice - deliveryFee - processingFee;
      } else if (applyDeliveryFee && !applyProcessingFee) {
        return this.totalBagPrice - deliveryFee;
      } else if (applyProcessingFee && !applyDeliveryFee) {
        return this.totalBagPrice - processingFee;
      } else return this.totalBagPrice;
    },
    afterDiscountBeforeFees() {
      if (this.applyMealPlanDiscount && this.deliveryPlan) {
        return this.preFeePreDiscount - this.mealPlanDiscount;
      } else return this.preFeePreDiscount;
    },
    afterDiscountAfterFees() {
      let applyDeliveryFee = this.storeSettings.applyDeliveryFee;
      let applyProcessingFee = this.storeSettings.applyProcessingFee;
      let deliveryFee = this.storeSettings.deliveryFee;
      let processingFee = this.storeSettings.processingFee;
      let salesTax = 1 + (this.salesTax);

      if (applyDeliveryFee && applyProcessingFee) {
        return (this.afterDiscountBeforeFees + deliveryFee + processingFee) * salesTax;
      } else if (applyDeliveryFee && !applyProcessingFee) {
        return (this.afterDiscountBeforeFees + deliveryFee) * salesTax;
      } else if (applyProcessingFee && !applyDeliveryFee) {
        return (this.afterDiscountBeforeFees + processingFee) * salesTax;
      } else return this.afterDiscountBeforeFees * salesTax;
    },
    applyMealPlanDiscount() {
      return this.storeSettings.applyMealPlanDiscount;
    },
    mealPlanDiscount() {
      return (
        this.preFeePreDiscount * (this.storeSettings.mealPlanDiscount / 100)
      );
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
      return this.storeSetting("next_orderable_delivery_dates", []).map(date => {
        return {
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        };
      });
    },
    tax() {
      return this.salesTax * this.afterDiscountBeforeFees;
    }
  },
  mounted() {
    if (this.deliveryDaysOptions.length === 1){
      this.deliveryDay = this.deliveryDaysOptions[0].value
    }
    this.getSalesTax(this.store.details.state);
  },
  methods: {
    ...mapActions(["refreshSubscriptions", "refreshCustomerOrders"]),
    ...mapMutations(["emptyBag"]),
    quantity(meal) {
      const qty = this.$store.getters.bagItemQuantity(meal);
      return qty;
    },
    addOne(meal) {
      this.$store.commit("addToBag", { meal, quantity: 1 });
    },
    minusOne(meal) {
      this.$store.commit("removeFromBag", { meal, quantity: 1 });
    },
    clearMeal(meal) {
      let quantity = this.quantity(meal);
      this.$store.commit("removeFromBag", { meal, quantity });
    },
    clearAll() {
      this.$store.commit("emptyBag");
    },
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
    addBagItems(bag) {
      this.$store.commit("addBagItems", bag);
    },
    checkout() {
      // this.loading = true;
      axios
        .post("/api/bag/checkout", {
          bag: this.bag,
          plan: this.deliveryPlan,
          pickup: this.pickup,
          delivery_day: this.deliveryDay,
          card_id: this.card,
          store_id: this.store.id,
          salesTax: this.tax
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
        });
    },
    getSalesTax(state){
      SalesTax.getSalesTax("US", state)
      .then((tax) => {
        this.setSalesTax(tax.rate);
      });
    },
    setSalesTax(rate){
      this.salesTax = rate;
    }
  }
}
</script>