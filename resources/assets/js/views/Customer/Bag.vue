<template>
  <div class="bag">
    <div class="card">
      <div class="card-body">
        <spinner v-if="loading" position="absolute"></spinner>
        <div class="row">
          <div class="col-sm-12 store-logo-area" v-if="!mobile">
            <logo-area></logo-area>
          </div>
          <div class="col-md-12 mb-2 bag-actions">
            <above-bag :manualOrder="manualOrder"></above-bag>
          </div>
        </div>
        <div class="row">
          <div class="col-md-5">
            <bag-area-bag :pickup="pickup"></bag-area-bag>
          </div>

          <div class="col-md-6 offset-md-1">
            <checkout-area
              :manualOrder="manualOrder"
              :mobile="mobile"
              :pickup="pickup"
              :subscriptionId="subscriptionId"
              :cashOrder="cashOrder"
              :creditCardId="creditCardId"
            ></checkout-area>
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
import Register from "../Register";

import MenuBag from "../../mixins/menuBag";
import states from "../../data/states.js";

import LogoArea from "../../components/Customer/LogoArea";
import AboveBag from "../../components/Customer/AboveBag";
import BagAreaBag from "../../components/Customer/BagAreaBag";
import CheckoutArea from "../../components/Customer/CheckoutArea";

export default {
  components: {
    cSwitch,
    Register,
    LogoArea,
    AboveBag,
    BagAreaBag,
    CheckoutArea
  },
  props: {
    manualOrder: false,
    subscriptionId: null
  },
  mixins: [MenuBag],
  data() {
    return {
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
      deliveryDay: undefined,
      stripeKey: window.app.stripe_key,
      loading: false,
      checkingOut: false,
      couponCode: "",
      couponClass: "checkout-item",
      deliveryFee: 0,
      amounts: {}
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
    mobile() {
      if (window.innerWidth < 500) return true;
      else return false;
    },
    storeSettings() {
      return this.store.settings;
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
  }
};
</script>
