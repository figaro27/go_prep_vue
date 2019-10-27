<template>
  <customer-bag
    :forceValue="forceValue"
    :preview="preview"
    :orderId="orderId"
    :storeView="storeView"
    :manualOrder="manualOrder"
    :checkoutDataProp="checkoutData"
    :adjustMealPlan="weeklySubscriptionValue"
  ></customer-bag>
</template>

<script>
import Spinner from "../../components/Spinner";
import { mapGetters, mapActions, mapMutations } from "vuex";
import CustomerBag from "../Customer/Bag";

export default {
  props: {
    preview: false
  },
  components: {
    Spinner,
    CustomerBag
  },
  /*data() {
    return {
      storeView: false,
      manualOrder: false,
      checkoutData: null
    };
  },*/
  computed: {
    ...mapGetters({
      isLoading: "isLoading"
    }),
    forceValue() {
      return this.$route.params.forceValue
        ? this.$route.params.forceValue
        : false;
    },
    orderId() {
      return this.$route.params.orderId;
    },
    storeView() {
      return this.$route.params.forceValue && this.$route.params.storeView
        ? this.$route.params.storeView
        : false;
    },
    manualOrder() {
      return this.$route.params.forceValue && this.$route.params.manualOrder
        ? this.$route.params.manualOrder
        : false;
    },
    checkoutData() {
      return this.$route.params.forceValue && this.$route.params.checkoutData
        ? this.$route.params.checkoutData
        : null;
    },
    weeklySubscriptionValue() {
      return this.$route.params.weeklySubscriptionValue;
    }
  },
  created() {
    this.setBagMealPlan(false);
  },
  mounted() {},
  methods: {
    ...mapActions({
      refreshViewedStore: "refreshViewedStore"
    }),
    ...mapMutations({
      setBagMealPlan: "setBagMealPlan"
    })
  }
};
</script>
