<template>
  <div class="bag container-fluid">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            <h2 class="center-text">Checkout</h2>
          </div>
          <div class="col-md-12 mb-2">
            <b-button variant="primary" @click="clearAll" class>Empty Bag</b-button>
            <router-link to="/customer/menu">
              <b-button variant="success" class="m-3">Change Meals</b-button>
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
                  <div class="col-sm-5 offset-1">{{ item.meal.title }}</div>
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
            <p
              v-if="total < minimum"
            >Please choose {{ remainingMeals }} {{ singOrPlural }} to continue.</p>
            <div></div>
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
                        checked
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
              <li class="checkout-item" v-if="storeSettings.applyDeliveryFee">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Delivery Fee:</strong>
                  </div>
                  <div class="col-md-8">
                    ${{ storeSettings.deliveryFee }}
                  </div>
                </div>

              </li>
              <li class="checkout-item">
                <div class="row">
                  <div class="col-md-4">
                    <span v-if="!applyMealPlanDiscount || !deliveryPlan">
                      <strong>Price:</strong>
                    </span>
                    <span v-if="applyMealPlanDiscount && deliveryPlan">
                      <strong>Price Before Discount:</strong>
                    </span>
                  </div>
                  <div class="col-md-8">
                    ${{ totalBagPrice }}
                  </div>
                </div>

              </li>
              <li class="checkout-item" v-if="deliveryPlan && applyMealPlanDiscount">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Weekly Meal Plan Discount:</strong>
                  </div>
                  <div class="col-md-4">
                    ${{ mealPlanDiscountAmount }}
                  </div>
                </div>

              </li>
              <li class="checkout-item" v-if="deliveryPlan && applyMealPlanDiscount">
                <div class="row">
                  <div class="col-md-4">
                    <strong>Weekly Meal Plan Price:</strong>
                  </div>
                  <div class="col-md-8">
                    ${{ totalBagPriceAfterDiscount }}
                  </div>
                </div>

              </li>
              <li class="checkout-item" v-if="storeSettings.allowPickup">
                <b-form-group>
                  <b-form-radio-group v-model="pickupOrDelivery" name="pickupOrDelivery">
                    <b-form-radio value="0">
                      <strong>Delivery</strong>
                    </b-form-radio>
                    <b-form-radio value="1" @click="pickupOrDelivery = 1">
                      <strong>Pickup</strong>
                    </b-form-radio>
                  </b-form-radio-group>
                </b-form-group>
              </li>
              <li class="checkout-item" v-if="storeSettings.allowPickup && pickupOrDelivery != 0">
                <p>
                  <strong>Pickup Instructions:</strong>
                  {{ storeSettings.pickupInstructions }}
                </p>
              </li>

              <li>
                <div>
                  <p v-if="pickupOrDelivery === '0'">Delivery Day</p>
                  <p v-if="pickupOrDelivery === '1'">Pickup Day</p>
                  <b-form-group
                    v-if="deliveryDaysOptions.length > 1"
                    description
                  >
                    <b-select :options="deliveryDaysOptions" v-model="deliveryDay" class="delivery-select" required>
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

              <li v-else>
                <div v-if="!willDeliver">
                  <b-alert variant="danger center-text" show>You are outside of the delivery area.</b-alert>
                </div>
                <div v-else>
                  <h4 class="mt-2 mb-3">Payment method</h4>
                  <card-picker :selectable="true" v-model="card"></card-picker>
                  <b-btn v-if="card" @click="checkout" class="menu-bag-btn">CHECKOUT</b-btn>
                  <!-- <img v-if="card" @click="checkout" src="/images/customer/checkout.jpg"> -->
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
      pickupOrDelivery: '0',
      stripeKey,
      stripeOptions,
      card: null
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
      willDeliver: "viewedStoreWillDeliver"
    }),
    storeSettings() {
      return this.store.settings;
    },
    minimum() {
      return this.storeSetting("minimum", 1);
    },
    remainingMeals() {
      return this.minimum - this.total;
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
      else return "One Time Order";
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
      axios
        .post("/api/bag/checkout", {
          bag: this.bag,
          plan: this.deliveryPlan,
          pickup: this.pickup,
          delivery_day: this.deliveryDay,
          card_id: this.card,
          store_id: this.store.id
        })
        .then(resp => {
          if (this.deliveryPlan) {
            this.$router.push("/customer/subscriptions");
          } else {
            this.$router.push("/customer/orders");
          }
        })
        .catch(e => {
          if (e.error && e.error.message) {
            alert(e.error.message);
          }
        })
        .finally(() => {});
    }
  }
};
</script>