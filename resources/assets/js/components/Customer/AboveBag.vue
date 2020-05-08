<template>
  <div>
    <b-button size="lg" class="brand-color white-text" :to="changeMealsRouter">
      <span class="d-sm-inline">Add Items</span>
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
  updated() {
    console.log("checkoutDataCustomer " + this.checkoutData.customer);
  },
  computed: {
    order() {
      return this.$route.params.order;
    },
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
        (this.$route.params.subscriptionId != null ||
          this.$route.query.sub === true)
      ) {
        let subId = this.$route.params.subscriptionId
          ? this.$route.params.subscriptionId
          : this.$route.query.subscriptionId;
        return {
          path: "/customer/subscriptions/" + subId,
          query: { sub: this.$route.query.sub }
        };
      } else if (
        this.$route.path === "/store/bag" &&
        this.$route.params.subscriptionId != null
      )
        return "/store/adjust-meal-plan/" + this.$route.params.subscriptionId;
      else if (
        this.$route.path === "/store/bag" &&
        this.$route.params.orderId != null
      ) {
        return {
          name: "store-adjust-order",
          params: {
            storeView: true,
            orderId: this.$route.params.orderId,
            order: this.order,
            forceValue: true,
            checkoutData: this.checkoutData,
            backFromBagPage: true
          }
        };
      } else if (
        this.$route.path === "/store/bag" &&
        this.$route.params.adjustOrder
      )
        return "/store/adjust-order/";
      else if (!this.$route.params.storeView) {
        return {
          name: "customer-menu",
          query: {
            r: this.$route.query.r
          },
          params: {
            checkoutData: this.checkoutData
          }
        };
      } else if (this.$route.params.preview) return "/store/menu/preview";
      else if (this.$route.params.manualOrder) return "/store/manual-order";
    }
  },
  methods: {
    setOrderLineItems(lineItemOrders) {
      this.checkoutData.lineItemOrders = lineItemOrders;
    }
  }
};
</script>
