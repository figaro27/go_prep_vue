<template>
  <div>
    <b-button size="lg" class="brand-color white-text" :to="changeMealsRouter">
      <span class="d-sm-inline">Change Items</span>
    </b-button>
    <b-button size="lg" class="gray white-text" @click="clearAll">
      <span class="d-sm-inline">Empty Bag</span>
    </b-button>
  </div>
</template>
<script>
import MenuBag from "../../mixins/menuBag";

export default {
  mixins: [MenuBag],
  props: {
    checkoutData: null
  },
  computed: {
    changeMealsRouter() {
      if (this.$route.params.forceValue && this.$route.params.manualOrder) {
        return {
          name: "store-manual-order",
          params: {
            storeView: true,
            manualOrder: true,
            forceValue: true,
            checkoutData: this.checkoutData
          }
        };
      } else if (
        this.$route.path === "/customer/bag" &&
        this.$route.params.subscriptionId != null
      )
        return "/customer/subscriptions/" + this.$route.params.subscriptionId;
      else if (
        this.$route.path === "/store/bag" &&
        this.$route.params.subscriptionId != null
      )
        return "/store/adjust-meal-plan/" + this.$route.params.subscriptionId;
      else if (
        this.$route.path === "/store/bag" &&
        this.$route.params.orderId != null
      )
        return "/store/adjust-order/" + this.$route.params.orderId;
      else if (
        this.$route.path === "/store/bag" &&
        this.$route.params.adjustOrder
      )
        return "/store/adjust-order/";
      else if (!this.$route.params.storeView) return "/customer/menu";
      else if (this.$route.params.preview) return "/store/menu/preview";
      else if (this.$route.params.manualOrder) return "/store/manual-order";
    }
  }
};
</script>
