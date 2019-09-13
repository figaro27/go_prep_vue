<template>
  <div class="main-customer-container box-shadow top-fill">
    <div class="bag">
      <auth-modal :showAuthModal="showAuthModal"></auth-modal>
      <spinner v-if="loading" position="absolute"></spinner>
      <div class="row">
        <div class="col-md-5 mb-2 bag-actions">
          <above-bag :manualOrder="manualOrder" class="mb-4"></above-bag>
          <bag-area :pickup="pickup"></bag-area>
          <bag-actions
            :manualOrder="manualOrder"
            :adjustOrder="adjustOrder"
            :adjustMealPlan="adjustMealPlan"
            :subscriptionId="subscriptionId"
          ></bag-actions>
        </div>

        <div class="col-md-6 offset-md-1">
          <checkout-area
            :manualOrder="manualOrder"
            :mobile="mobile"
            :pickup="pickup"
            :subscriptionId="subscriptionId"
            :cashOrder="cashOrder"
            :creditCardId="creditCardId"
            :creditCardList="creditCardList"
            :salesTax="salesTax"
            :customer="customer"
            :preview="preview"
          ></checkout-area>
          <store-closed></store-closed>
        </div>
      </div>
      <add-customer-modal
        :addCustomerModal="addCustomerModal"
      ></add-customer-modal>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
import { createToken } from "vue-stripe-elements-plus";
import Register from "../Register";
import SalesTax from "sales-tax";

import MenuBag from "../../mixins/menuBag";
import states from "../../data/states.js";

import AboveBag from "../../components/Customer/AboveBag";
import BagArea from "../../components/Customer/BagArea";
import CheckoutArea from "../../components/Customer/CheckoutArea";
import AddCustomerModal from "../../components/Customer/AddCustomerModal";
import BagActions from "../../components/Customer/BagActions";
import AuthModal from "../../components/Customer/AuthModal";

export default {
  components: {
    cSwitch,
    Register,
    AboveBag,
    BagArea,
    CheckoutArea,
    AddCustomerModal,
    SalesTax,
    BagActions,
    AuthModal
  },
  props: {
    manualOrder: false,
    subscriptionId: null,
    adjustOrder: false,
    adjustMealPlan: false,
    preview: false
  },
  mixins: [MenuBag],
  data() {
    return {
      showAuthModal: false,
      transferTime: "",
      cashOrder: false,
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
      amounts: {},
      salesTax: 0
    };
  },
  watch: {
    deliveryDaysOptions(val) {
      if (!this.deliveryDay && val[0]) {
        this.deliveryDay = val[0].value;
      }
    }
  },
  created() {
    this.$eventBus.$on("showAuthModal", () => {
      this.showAuthModal = true;
    });
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

    SalesTax.getSalesTax("US", this.store.details.state).then(tax => {
      this.setSalesTax(tax.rate);
    });

    if (!_.includes(this.transferType, "delivery")) this.pickup = 1;

    this.selectedPickupLocation = this.pickupLocationOptions[0].value;

    if (!this.deliveryDay && this.deliveryDaysOptions) {
      this.deliveryDay = this.deliveryDaysOptions[0].value;
    }
  },
  updated() {
    this.creditCardId = this.card;
  },
  setSalesTax(rate) {
    this.salesTax = rate;
  }
};
</script>
