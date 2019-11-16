<template>
  <div>
    <!-- <Spinner v-if="loading" /> -->
    <customer-menu
      :pickup="pickup"
      :transferTime="transferTime"
      :deliveryDay="deliveryDay"
      :manualOrder="manualOrder"
      :storeView="storeView"
      :forceValue="forceValue"
      :checkoutData="checkoutData"
      :cashOrder="cashOrder"
    ></customer-menu>
    <!--<store-bag></store-bag>!-->
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import { mapGetters, mapActions, mapMutations } from "vuex";
import CustomerMenu from "../Customer/Menu";
import StoreBag from "./Bag";

export default {
  components: {
    Spinner,
    CustomerMenu,
    StoreBag
  },
  data() {
    return {
      loading: true
    };
  },
  computed: {
    ...mapGetters({
      isLoading: "isLoading"
    }),
    storeView: function() {
      return this.$route.params.storeView ? this.$route.params.storeView : true;
    },
    manualOrder: function() {
      return this.$route.params.manualOrder
        ? this.$route.params.manualOrder
        : true;
    },
    forceValue: function() {
      return this.$route.params.forceValue
        ? this.$route.params.forceValue
        : true;
    },
    checkoutData: function() {
      return this.$route.params.checkoutData
        ? this.$route.params.checkoutData
        : null;
    },
    pickup: function() {
      if (
        this.$route.params.checkoutData &&
        this.$route.params.checkoutData.hasOwnProperty("pickup")
      ) {
        return this.$route.params.checkoutData.pickup;
      }

      return null;
    },
    transferTime: function() {
      if (
        this.$route.params.checkoutData &&
        this.$route.params.checkoutData.hasOwnProperty("transferTime")
      ) {
        return this.$route.params.checkoutData.transferTime;
      }

      return null;
    },
    deliveryDay: function() {
      if (
        this.$route.params.checkoutData &&
        this.$route.params.checkoutData.hasOwnProperty("deliveryDay")
      ) {
        return this.$route.params.checkoutData.deliveryDay;
      }

      return null;
    },
    cashOrder: function() {
      if (
        this.$route.params.checkoutData &&
        this.$route.params.checkoutData.hasOwnProperty("cashOrder")
      ) {
        return this.$route.params.checkoutData.cashOrder;
      }

      return null;
    }
  },
  created() {},
  mounted() {
    if (
      this.$route.path === "/store/manual-order" &&
      !this.$route.params.storeView
    ) {
      this.$router.push({ path: "/store/orders" });
    }
  },
  methods: {
    ...mapActions({}),
    setLoadingToFalse() {
      this.loading = false;
    }
  }
};
</script>
