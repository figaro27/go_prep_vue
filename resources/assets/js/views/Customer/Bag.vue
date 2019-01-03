<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3>Bag</h3>
                        <b-col v-for="(item, mealId) in bag" :key="`bag-${mealId}`" cols="12">
                        <img src="/storage/x.png" @click="clearMeal(item.meal)">
                        <img src="/storage/minus.jpg" @click="minusOne(item.meal)">
                            {{ item.quantity }}
                        <img src="/storage/plus.jpg" @click="addOne(item.meal)">
                        <img :src="item.meal.featured_image" class="cart-item-img">
                        {{ item.meal.title }}
                        ${{ item.meal.price }}
                        <hr>
                        </b-col>
                        <b-button @click="clearAll">Empty Cart</b-button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        Weekly Meal Plan
                        <div class="aside-options">
                        <c-switch color="success" variant="pill" size="lg" v-model="deliveryPlan" checked/>
                    </div>
                        <hr>
                        {{ total }} {{ singOrPluralTotal }} {{ deliveryPlanText }}
                        <hr>
                        <p v-if="total < minimum">
                        Please choose {{ remainingMeals }} {{ singOrPlural }} to continue.`
                        </p>
                        <p v-if="storeSettings.applyDeliveryFee">
                          Delivery Fee: ${{ storeSettings.deliveryFee }}
                        </p>
                        <hr>
                        Price: ${{ totalBagPrice }}
                        <hr>
                        <div v-if="deliveryPlan">
                            Weekly Meal Plan Discount ${{ mealPlanDiscountAmount }}
                            <hr>
                            Weekly Meal Plan Price: ${{ totalBagPriceAfterDiscount }}
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
                        <div>
                          <router-link to="/customer/menu">
                            <img v-if="total < minimum" src="/storage/back.jpg">
                          </router-link>
                            <img v-if="total >= minimum" src="/storage/checkout.jpg">
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
import { Switch as cSwitch } from '@coreui/vue'
    export default {
        components: {
            cSwitch
        },
        data(){
            return {
                deliveryPlan: false,
                mealPlanDiscountPercent: 10, // Hard coding for now until we do Store Settings. Will also move to the store.,
                pickup: ''
            }
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
              return this.storeSettings.minimum;
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
            singOrPluralTotal(){
                if (this.total > 1){
                    return "meals";
                }
                return "meal";
            },
            deliveryPlanText(){
                if (this.deliveryPlan)
                    return "Prepared Weekly"
                else
                    return "One Time Order"
            },
            totalBagPriceAfterDiscount(){
                return ((this.totalBagPrice * (100 - this.mealPlanDiscountPercent))/100).toFixed(2);
            },
            mealPlanDiscountAmount(){
                return (this.totalBagPrice - (this.totalBagPrice * (100 - this.mealPlanDiscountPercent)/100)).toFixed(2);
            }
        },
        mounted()
        {
        },
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
        }
    }
</script>