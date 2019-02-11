<template>
  <div class="bag container-fluid">
    <div class="card">
      <div class="card-body">
        <spinner v-if="loading" position="absolute"></spinner>
        <div class="row">
          <div class="col-md-12">
            <h2 class="center-text">Checkout</h2>
          </div>
          <div class="col-md-12 mb-2">
            <b-button variant="primary" @click="clearAll" class>Empty Bag</b-button>
            <router-link to="/customer/menu">
              <b-button variant="warning" class="m-3">Change Meals</b-button>
            </router-link>
          </div>
        </div>
        <div class="row">
          <div class="col-md-5">
            <ul class="list-group">
              <li v-for="item in bag" :key="`bag-${item.meal.id}`" class="bag-item">
                <div v-if="item.quantity > 0" class="row">
                  <div class="col-sm-1">
                    <img
                      src="/images/customer/bag-plus.png"
                      @click="addOne(item.meal)"
                      class="pl-1"
                    >
                    <p class="bag-quantity">{{ item.quantity }}</p>
                    <img
                      src="/images/customer/bag-minus.png"
                      @click="minusOne(item.meal)"
                      class="pl-1"
                    >
                  </div>
                  <div class="col-sm-2">
                    <img :src="item.meal.featured_image" class="bag-item-img">
                  </div>
                  <div class="col-sm-5 offset-1">
                    <p>{{ item.meal.title }}</p>
                    <p class="strong">{{ format.money(item.meal.price * item.quantity) }}</p>
                  </div>
                  <div class="col-sm-2">
                    <img
                      src="/images/customer/x.png"
                      @click="clearMeal(item.meal)"
                      class="clear-meal"
                    >
                  </div>
                </div>
              </li>
            </ul>
            <p class="mt-3" v-if="minOption === 'meals' && total < minMeals">
              Please add {{ remainingMeals }} {{ singOrPlural }} to continue.
            </p>
            <router-link to="/customer/menu">
              <b-btn v-if="minOption === 'meals' && total < minMeals && !preview" class="menu-bag-btn">BACK</b-btn>
            </router-link>
            
            <p class="mt-3" v-if="minOption === 'price' && totalBagPrice < minPrice">
                  Please add {{format.money(remainingPrice)}} more to continue.
            </p>
            <div>
              <router-link to="/customer/menu">
                <b-btn v-if="minOption === 'price' && totalBagPrice <= minPrice && !preview" class="menu-bag-btn">BACK</b-btn>
              </router-link>
            </div>

          </div>
          <div class="col-md-6 offset-1">
            <ul class="list-group">
              <li class="bag-item">
                <div class="row">
                  <div class="col-md-3">
                    <p>
                      <strong>Weekly Meal Plan</strong>
                    </p>
                  </div>
                  <div class="col-md-4">
                    <div class="aside-options">
                      <c-switch
                        color="success"
                        variant="pill"
                        size="lg"
                        v-model="deliveryPlan"
                      />
                    </div>
                  </div>
                </div>
              </li>
              <li class="checkout-item">
                <p>
                  <strong>{{ total }} {{ singOrPluralTotal }} {{ deliveryPlanText }}</strong>
                </p>
              </li>
              <li class="checkout-item" v-if="storeSettings.applyDeliveryFee || storeSettings.applyProcessingFee">
                <div class="row">
                  <div class="col-md-4">
                    <strong>SubTotal:</strong>
                  </div>
                  <div class="col-md-3 offset-5">{{ format.money(totalBagPriceBeforeFees) }}</div>
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
                    <span v-if="!applyMealPlanDiscount || !deliveryPlan">
                      <strong>Total:</strong>
                    </span>
                    <span v-if="applyMealPlanDiscount && deliveryPlan">
                      <strong>SubTotal:</strong>
                    </span>
                  </div>
                  <div class="col-md-3 offset-5">{{ format.money(totalBagPrice) }}</div>
                </div>
              </li>
              <li class="checkout-item" v-if="deliveryPlan && applyMealPlanDiscount">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Weekly Meal Plan Discount:</strong>
                  </div>
                  <div class="col-md-3 offset-5 red">({{ format.money(mealPlanDiscountAmount) }})</div>
                </div>
              </li>
              <li class="checkout-item" v-if="deliveryPlan && applyMealPlanDiscount">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Total:</strong>
                  </div>
                  <div class="col-md-3 offset-5">{{ format.money(totalBagPriceAfterDiscount) }}</div>
                </div>
              </li>
              <li class="checkout-item" v-if="storeSettings.allowPickup">
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
              <li class="checkout-item" v-if="storeSettings.allowPickup && pickup != 0">
                <p>
                  <strong>Pickup Instructions:</strong>
                  {{ storeSettings.pickupInstructions }}
                </p>
              </li>

              <li>
                <div>
                  <p v-if="pickup === false">Delivery Day</p>
                  <p v-if="pickup === true">Pickup Day</p>
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
                    <p>Delivery day: {{ deliveryDaysOptions[0].text }}</p>
                  </div>
                </div>
              </li>

              <li class="checkout-item" v-if="total < minimum">
                <p>Please choose {{ remainingMeals }} {{ singOrPlural }} to continue.`</p>
              </li>

              <li v-else-if="loggedIn">
                <div v-if="!willDeliver && pickup != 1">
                  <b-alert variant="danger center-text" show>You are outside of the delivery area.</b-alert>
                </div>
                <div v-else>
                  <h4 class="mt-2 mb-3">Choose Payment Method</h4>
                  <card-picker :selectable="true" v-model="card"></card-picker>
                  <b-btn v-if="card && minOption === 'meals' && total >= minMeals" @click="checkout" class="menu-bag-btn">CHECKOUT</b-btn>
                  <b-btn v-if="card && minOption === 'price' && totalBagPrice >= minPrice" @click="checkout" class="menu-bag-btn">CHECKOUT</b-btn>
                </div>
              </li>

              <li v-else>
                <div class="row">
                  <div class="col-md-6">
                    <b-btn class="menu-bag-btn">
                      <router-link
                        :to="{ path: '/login', query: { redirect: '/customer/bag' } }"
                      >LOG IN</router-link>
                    </b-btn>
                  </div>
                  <div class="col-md-6">
                    <b-btn class="menu-bag-btn">
                      <router-link :to="{ path: '/register' }">REGISTER</router-link>
                    </b-btn>
                  </div>
                </div>
              </li>
            </ul>
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

import CardPicker from "../../components/Billing/CardPicker";

export default {
  components: {
    cSwitch,
    CardPicker
  },
  data() {
    return {
      deliveryPlan: false,
      pickup: false,
      deliveryDay: undefined,
      stripeKey,
      stripeOptions,
      card: null,
      loading: false
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
      minPrice: 'minimumPrice'
    }),
    storeSettings() {
      return this.store.settings;
    },
    minimumOption() {
      return this.minOption;
    },
    minimumMeals(){
      return this.minMeals;
    },
    minimumPrice(){
      return this.minPrice;
    },
    remainingMeals() {
      return this.minMeals - this.total;
    },
    remainingPrice() {
      return this.minPrice - this.totalBagPrice;
    },
    totalBagPriceBeforeFees(){
      let deliveryFee = this.storeSettings.deliveryFee;
      let processingFee = this.storeSettings.processingFee;
      let applyDeliveryFee = this.storeSettings.applyDeliveryFee;
      let applyProcessingFee = this.storeSettings.applyProcessingFee;

      if (applyDeliveryFee && applyProcessingFee){
        return this.totalBagPrice - deliveryFee - processingFee;
      }
      else if (applyDeliveryFee && !applyProcessingFee){
        return this.totalBagPrice - deliveryFee;
      }
      else if (applyProcessingFee && !applyDeliveryFee){
        return this.totalBagPrice - processingFee;
      }
      
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
    totalBagPriceAfterDiscount() {
      return (
        (this.totalBagPrice * (100 - this.mealPlanDiscount)) /
        100
      ).toFixed(2);
    },
    mealPlanDiscountAmount() {
      return (
        this.totalBagPrice -
        (this.totalBagPrice * (100 - this.mealPlanDiscount)) / 100
      ).toFixed(2);
    },
    deliveryDaysOptions() {
      return this.storeSetting("next_delivery_dates", []).map(date => {
        return {
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        };
      });
    },
    applyMealPlanDiscount() {
      return this.storeSettings.applyMealPlanDiscount;
    },
    mealPlanDiscount() {
      return this.storeSettings.mealPlanDiscount;
    }
  },
  mounted() {},
  methods: {
    ...mapActions(['refreshSubscriptions', 'refreshCustomerOrders']),
    ...mapMutations(['emptyBag']),
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
      this.loading = true;
      axios
        .post("/api/bag/checkout", {
          bag: this.bag,
          plan: this.deliveryPlan,
          pickup: this.pickup,
          delivery_day: this.deliveryDay,
          card_id: this.card,
          store_id: this.store.id
        })
        .then(async resp => {
          this.emptyBag();
          
          if (this.deliveryPlan) {
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
          let error = _.first(Object.values(response.response.data.errors)) || ['Please try again'];
          error = error.join(" ");
          this.$toastr.e(error, "Error");
        })
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>