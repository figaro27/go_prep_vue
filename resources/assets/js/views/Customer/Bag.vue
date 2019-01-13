<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3>Bag</h3>
                        <b-col v-for="(item, mealId) in bag" :key="`bag-${mealId}`" cols="12">
                        <img src="/images/customer/x.png" @click="clearMeal(item.meal)">
                        <img src="/images/customer/minus.jpg" @click="minusOne(item.meal)">
                            <p>{{ item.quantity }}</p>
                        <img src="/images/customer/plus.jpg" @click="addOne(item.meal)">
                        <img :src="item.meal.featured_image" class="cart-item-img">
                        <p>{{ item.meal.title }}</p>
                        <p>${{ item.meal.price }}</p>
                        <hr>
                        </b-col>
                        <b-button @click="clearAll">Empty Cart</b-button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p>Weekly Meal Plan</p>
                        <div class="aside-options">
                        <c-switch color="success" variant="pill" size="lg" v-model="deliveryPlan" checked/>
                    </div>
                        <hr>
                        <p>{{ total }} {{ singOrPluralTotal }} {{ deliveryPlanText }}</p>
                        <hr>
                        <p v-if="total < minimum">
                        Please choose {{ remainingMeals }} {{ singOrPlural }} to continue.`
                        </p>
                        <p v-if="storeSettings.applyDeliveryFee">
                          Delivery Fee: ${{ storeSettings.deliveryFee }}
                        </p>
                        <hr>
                        <p>Price: ${{ totalBagPrice }}</p>
                        <hr>
                        <div v-if="deliveryPlan">
                            <p>Weekly Meal Plan Discount ${{ mealPlanDiscountAmount }}</p>
                            <hr>
                            <p>Weekly Meal Plan Price: ${{ totalBagPriceAfterDiscount }}</p>
                        </div>
                        <div v-if="storeSettings.allowPickup">
                          <hr>
                         <b-form-group>
                          <b-form-radio-group v-model="pickup" name="pickupOrDelivery">
                            <b-form-radio value=1>Pickup</b-form-radio>
                            <b-form-radio value=0>Delivery</b-form-radio>
                          </b-form-radio-group>
                        </b-form-group>
                        <p v-if="pickup > 0">{{ storeSettings.pickupInstructions }} </p>
                        
                        </div>
                        <div v-if="!storeSettings.allowPickup || pickup === 1">
                          <b-form-group v-if="deliveryDaysOptions.length > 1" label="Delivery day" description="">
                            <b-select :options="deliveryDaysOptions" v-model="deliveryDay" required>
                              <option slot="top" disabled>-- Select delivery day --</option>
                            </b-select>
                          </b-form-group>
                          <div v-else-if="deliveryDaysOptions.length === 1">
                            <p>Delivery day: {{ deliveryDaysOptions[0].text }}</p>
                          </div>
                        </div>
                        <div>
                          <router-link to="/customer/menu" v-if="total < minimum">
                            <img src="/images/customer/back.jpg">
                          </router-link>
                          <div v-else>Test card:
                            <pre>
                              4242424242424242
                            </pre>
                            <card
                              class="stripe-card"
                              :class="{ card }"
                              :stripe="stripeKey"
                              :options="stripeOptions"
                              @change="card = $event.complete"
                            />
                            <img v-if="card" @click="checkout" src="/images/customer/checkout.jpg">
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
              <router-link to="/customer/menu" v-if="total < minimum">
                <img src="/storage/back.jpg">
              </router-link>

              <!--<router-link to="/customer/checkout" v-else>-->
              <div v-else>Test card:
                <pre>
                  4242424242424242
                </pre>
                <card
                  class="stripe-card"
                  :class="{ card }"
                  :stripe="stripeKey"
                  :options="stripeOptions"
                  @change="card = $event.complete"
                />
                <img v-if="card" @click="checkout" src="/storage/checkout.jpg">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.menu-item {
  margin-bottom: 10px;
}

.menu-item-img {
  width: 100%;
}

.cart-item-img {
  height: 100px;
}

.quantity {
  width: 75px;
  border-radius: 10px;
  opacity: 0.5;
  text-align: center;
}
</style>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import { stripeKey, stripeOptions } from "../../config/stripe.json";
import { createToken } from "vue-stripe-elements-plus";

export default {
  components: {
    cSwitch
  },
  data() {
    return {
      deliveryPlan: false,
      mealPlanDiscountPercent: 10, // Hard coding for now until we do Store Settings. Will also move to the store.,
      pickup: 0,
      deliveryDay: undefined,
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
      totalBagPrice: "totalBagPrice"
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
        return "meals";
      }
      return "meal";
    },
    deliveryPlanText() {
      if (this.deliveryPlan) return "Prepared Weekly";
      else return "One Time Order";
    },
    totalBagPriceAfterDiscount() {
      return (
        (this.totalBagPrice * (100 - this.mealPlanDiscountPercent)) /
        100
      ).toFixed(2);
    },
    mealPlanDiscountAmount() {
      return (
        this.totalBagPrice -
        (this.totalBagPrice * (100 - this.mealPlanDiscountPercent)) / 100
      ).toFixed(2);
    },
    deliveryDaysOptions() {
      return this.storeSetting("next_delivery_dates", []).map(date => {
        return {
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        };
      });
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
      createToken().then(data => {
        console.log(data);

        if (!data.token) {
          throw new Error("Failed to save card", data);
        }

        axios
          .post("/api/bag/checkout", {
            bag: this.bag,
            plan: this.deliveryPlan,
            pickup: this.pickup,
            delivery_day: this.deliveryDay,
            token: data.token,
            store_id: this.store.id
          })
          .then(resp => {
            if (this.deliveryPlan) {
              this.$router.push("/customer/account/subscriptions");
            } else {
              this.$router.push("/customer/account/orders");
            }
          })
          .catch(e => {
            if (e.error && e.error.message) {
              alert(e.error.message);
            }
          })
          .finally(() => {});
      });
    }
  }
};
</script>