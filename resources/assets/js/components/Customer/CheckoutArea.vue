<template>
  <div>
    <ul class="list-group">
      <li class="bag-item" v-if="storeSettings.allowMealPlans">
        <div class="row" v-if="!manualOrder">
          <div class="col-md-8 pb-1">
            <h3>
              <strong
                >Weekly Meal Plan
                <span v-if="storeSettings.applyMealPlanDiscount"
                  >{{ storeSettings.mealPlanDiscount }}% Off</span
                ></strong
              >
              <img
                v-if="!mobile"
                v-b-popover.hover="
                  'Choose a weekly meal plan instead of a one time order and meals will be given to you on a weekly basis. You can swap out meals as well as pause or cancel the meal plan at any time. This will apply to the following week\'s renewal.'
                "
                title="Weekly Meal Plan"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
              <img
                v-if="mobile"
                v-b-popover.click.top="
                  'Choose a weekly meal plan instead of a one time order and meals will be given to you on a weekly basis. You can swap out meals as well as pause or cancel the meal plan at any time. This will apply to the following week\'s renewal.'
                "
                title="Weekly Meal Plan"
                src="/images/store/popover.png"
                class="popover-size ml-1"
              />
            </h3>
          </div>
        </div>
        <div class="row" v-if="!manualOrder">
          <div class="col-md-9">
            <strong
              ><p class="mr-1">
                <span v-if="storeSettings.applyMealPlanDiscount">
                  Create a meal plan and you'll save
                  <span class="text-success standout">{{
                    format.money(mealPlanDiscount, storeSettings.currency)
                  }}</span>
                  on each order.
                </span>
                <c-switch
                  color="success"
                  variant="pill"
                  size="lg"
                  :checked="deliveryPlan"
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
      <li class="checkout-item" v-if="!manualOrder">
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
      <li class="checkout-item" v-if="deliveryPlan && applyMealPlanDiscount">
        <div class="row">
          <div class="col-6 col-md-4">
            <strong>Meal Plan Discount:</strong>
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
          minOption === 'price' &&
            totalBagPricePreFees < minPrice &&
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
              (manualOrder && cards.length > 0) ||
                (cashOrder && customer != null)
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
    </ul>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import MenuBag from "../../mixins/menuBag";

export default {
  props: {
    manualOrder: false
  },
  mixins: [MenuBag],
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCustomers: "storeCustomers",
      storeSettings: "viewedStoreSetting",
      total: "bagQuantity",
      allergies: "allergies",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      willDeliver: "viewedStoreWillDeliver",
      _categories: "viewedStoreCategories",
      storeLogo: "viewedStoreLogo",
      isLoading: "isLoading",
      totalBagPricePreFees: "totalBagPricePreFees",
      totalBagPrice: "totalBagPrice",
      loggedIn: "loggedIn",
      minOption: "minimumOption",
      minMeals: "minimumMeals",
      minPrice: "minimumPrice",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    remainingPrice() {
      return this.minPrice - this.totalBagPricePreFees;
    }
  }
};
</script>
