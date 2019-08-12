<template>
  <div class="bag">
    <div class="card">
      <div class="card-body">
        <spinner v-if="loading" position="absolute"></spinner>
        <div class="row">
          <div class="col-sm-12 store-logo-area" v-if="!mobile">
            <a :href="storeWebsite" v-if="storeWebsite != null">
              <img
                v-if="storeLogo.url_thumb"
                class="store-logo"
                :src="storeLogo.url_thumb"
                alt="Company Logo"
              />
            </a>
            <img
              v-if="storeLogo && storeWebsite === null"
              class="store-logo"
              :src="storeLogo.url_thumb"
              alt="Company Logo"
            />
          </div>
          <div class="col-md-12 mb-2 bag-actions">
            <b-button
              size="lg"
              class="brand-color white-text"
              to="/customer/menu"
              v-if="!manualOrder"
            >
              <span class="d-sm-inline">Change Meals</span>
            </b-button>
            <b-button
              size="lg"
              class="brand-color white-text"
              to="/store/manual-order"
              v-if="manualOrder"
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
                      v-if="!item.meal_package"
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
                    <div
                      v-if="item.meal_package"
                      @click="
                        addOne(
                          item.meal,
                          true,
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
            <p
              class="mt-3"
              v-if="minOption === 'meals' && total < minMeals && !manualOrder"
            >
              Please add {{ remainingMeals }} {{ singOrPlural }} to continue.
            </p>
            <router-link to="/customer/menu">
              <b-btn
                v-if="
                  minOption === 'meals' &&
                    total < minMeals &&
                    !preview &&
                    !manualOrder
                "
                class="menu-bag-btn mb-2"
                >BACK</b-btn
              >
            </router-link>
            <router-link to="/store/manual-order">
              <b-btn
                v-if="
                  minOption === 'meals' &&
                    total < minMeals &&
                    !preview &&
                    manualOrder
                "
                class="menu-bag-btn mb-2"
                >BACK</b-btn
              >
            </router-link>

            <p
              class="mt-3"
              v-if="
                minOption === 'price' &&
                  totalBagPricePreFees < minPrice &&
                  !manualOrder
              "
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
                      totalBagPricePreFees <= minPrice &&
                      !preview &&
                      !manualOrder
                  "
                  class="menu-bag-btn"
                  >BACK</b-btn
                >
              </router-link>
              <router-link to="/store/manual-order">
                <b-btn
                  v-if="
                    minOption === 'price' &&
                      totalBagPricePreFees <= minPrice &&
                      !preview &&
                      manualOrder
                  "
                  class="menu-bag-btn"
                  >BACK</b-btn
                >
              </router-link>
            </div>
          </div>
          <div class="col-md-6 offset-md-1">
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
                            format.money(
                              mealPlanDiscount,
                              storeSettings.currency
                            )
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
                v-if="
                  storeSettings.applyDeliveryFee &&
                    pickup === 0 &&
                    !couponFreeDelivery
                "
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
                      format.money(processingFeeAmount, storeSettings.currency)
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
                        <option slot="top" disabled
                          >-- Select Customer --</option
                        >
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
                    v-if="
                      manualOrder && storeModules.cashOrders && !subscriptionId
                    "
                    v-model="cashOrder"
                    class="pb-2"
                  >
                    Cash Order
                  </b-form-checkbox>
                  <card-picker
                    :selectable="true"
                    v-model="card"
                    v-if="!manualOrder && !subscriptionId"
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
                      <b-form-group
                        v-if="manualOrder"
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
              <p v-html="deliveryInstructions"></p>
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
    <b-modal
      size="lg"
      title="Add New Customer"
      v-model="addCustomerModal"
      v-if="addCustomerModal"
      hide-footer
    >
      <b-form @submit.prevent="addCustomer" class="mt-3">
        <b-form-group horizontal label="First Name">
          <b-form-input
            v-model="form.first_name"
            type="text"
            required
            placeholder="First name"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Last Name">
          <b-form-input
            v-model="form.last_name"
            type="text"
            required
            placeholder="Last name"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Email">
          <b-form-input
            v-model="form.email"
            type="email"
            required
            placeholder="Enter email"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Phone">
          <b-form-input
            v-model="form.phone"
            type="text"
            required
            placeholder="Phone"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Address">
          <b-form-input
            v-model="form.address"
            type="text"
            required
            placeholder="Address"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="City">
          <b-form-input
            v-model="form.city"
            type="text"
            required
            placeholder="City"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="State">
          <v-select
            label="name"
            :options="stateNames"
            :on-change="val => changeState(val)"
          ></v-select>
        </b-form-group>
        <b-form-group horizontal label="Zip">
          <b-form-input
            v-model="form.zip"
            type="text"
            required
            placeholder="Zip"
          ></b-form-input>
        </b-form-group>
        <b-form-group horizontal label="Delivery">
          <b-form-input
            v-model="form.delivery"
            type="text"
            placeholder="Delivery Instructions"
          ></b-form-input>
        </b-form-group>
        <b-form-checkbox
          id="accepted-tos"
          name="accepted-tos"
          v-model="form.accepted_tos"
          :value="1"
          :unchecked-value="0"
        >
          This customer gave me permission to create their account and accepts
          the
          <a href="https://www.goprep.com/terms-of-service/" target="_blank"
            ><span class="strong">terms of service</span></a
          >
        </b-form-checkbox>
        <b-button type="submit" variant="primary" class="float-right"
          >Add</b-button
        >
      </b-form>
    </b-modal>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import { createToken } from "vue-stripe-elements-plus";
import SalesTax from "sales-tax";
import Register from "../Register";

import MenuBag from "../../mixins/menuBag";
import CardPicker from "../../components/Billing/CardPicker";
import states from "../../data/states.js";

export default {
  components: {
    cSwitch,
    CardPicker,
    SalesTax,
    Register
  },
  props: {
    manualOrder: {
      default: false
    }
  },
  mixins: [MenuBag],
  data() {
    return {
      //couponFreeDelivery: 0,
      transferTime: "",
      cashOrder: false,
      form: {},
      addCustomerModal: false,
      deposit: 100,
      creditCardList: [],
      creditCard: {},
      creditCardId: null,
      customer: null,
      selectedPickupLocation: null,
      pickup: 0,
      //deliveryPlan: false,
      deliveryDay: undefined,
      stripeKey: window.app.stripe_key,
      loading: false,
      checkingOut: false,
      salesTax: 0,
      couponCode: "",
      //couponApplied: false,
      couponClass: "checkout-item",
      deliveryFee: 0
    };
  },
  watch: {
    deliveryDaysOptions(val) {
      if (!this.deliveryDay && val[0]) {
        this.deliveryDay = val[0].value;
      }
    }
  },
  computed: {
    ...mapGetters({
      creditCards: "cards",
      store: "viewedStore",
      storeSetting: "viewedStoreSetting",
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

      let grouped = {};
      customers.forEach(customer => {
        grouped[customer.id] = customer.name;
      });
      return grouped;
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
    cards() {
      if (this.manualOrder) {
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
    storeSettings() {
      return this.store.settings;
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
      if (this.applyMealPlanDiscount && this.deliveryPlan) {
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
    },
    stateNames() {
      return states.stateNames();
    },
    subscriptionId() {
      return this.$route.params.subscriptionId;
    }
  },
  mounted() {
    this.creditCardId = this.card;
    if (this.storeSettings.salesTax > 0) {
      this.salesTax = this.storeSettings.salesTax / 100;
    } else {
      this.getSalesTax(this.store.details.state);
    }

    if (!_.includes(this.transferType, "delivery")) this.pickup = 1;

    this.selectedPickupLocation = this.pickupLocationOptions[0].value;

    if (!this.deliveryDay && this.deliveryDaysOptions) {
      this.deliveryDay = this.deliveryDaysOptions[0].value;
    }
  },
  updated() {
    this.creditCardId = this.card;
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
    preventNegative() {
      if (this.total < 0) {
        this.total += 1;
      }
    },
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

      // this.loading = true;
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
    showAddCustomerModal() {
      this.addCustomerModal = true;
    },
    addCustomer() {
      let form = this.form;

      if (!form.accepted_tos) {
        this.$toastr.e(
          "Please accept the terms of service.",
          "Registration failed"
        );
        return;
      }

      axios
        .post("/api/me/register", form)
        .then(async response => {
          this.addCustomerModal = false;
          this.form = {};
          await this.refreshStoreCustomers();
        })
        .catch(e => {
          this.$toastr.e("Please try again.", "Registration failed");
        });
    },
    changeState(state) {
      this.form.state = state.abbreviation;
    },
    async updateSubscriptionMeals() {
      if (this.$route.params.mealPlanAdjustment) {
        axios
          .post("/api/me/subscriptions/updateMeals", {
            bag: this.bag,
            salesTaxRate: this.salesTax,
            subscriptionId: this.subscriptionId
          })
          .then(resp => {
            this.refreshStoreSubscriptions();
            this.emptyBag();
            this.setBagMealPlan(false);
            this.setBagCoupon(null);
            this.$router.push({
              path: "/store/meal-plans",
              query: {
                updated: true
              }
            });
          });
      } else {
        try {
          const { data } = await axios.post(
            `/api/me/subscriptions/${this.subscriptionId}/meals`,
            { bag: this.bag, salesTaxRate: this.salesTax }
          );
          await this.refreshSubscriptions();
          this.emptyBag();
          this.setBagMealPlan(false);
          this.setBagCoupon(null);

          this.$router.push({
            path: "/customer/meal-plans",
            query: {
              updated: true
            }
          });
        } catch (e) {
          if (!_.isEmpty(e.response.data.error)) {
            this.$toastr.e(e.response.data.error);
          } else {
            this.$toastr.e(
              "Please try again or contact our support team",
              "Failed to update meals!"
            );
          }
          return;
        }
      }
    }
  }
};
</script>
