<template>
  <div>
    <!-- <Spinner v-if="loading" /> -->
    <customer-menu
      :adjustOrder="true"
      :orderId="orderId"
      :storeView="true"
    ></customer-menu>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import format from "../../lib/format.js";
import Spinner from "../../components/Spinner";
import MenuBag from "../../mixins/menuBag";
import CustomerMenu from "../Customer/Menu";

export default {
  components: {
    Spinner,
    CustomerMenu
  },
  mixins: [MenuBag],
  data() {
    return {
      isLoading: false
    };
  },
  computed: {
    ...mapGetters({
      upcomingOrders: "storeUpcomingOrders",
      store: "store",
      bag: "bag",
      getMeal: "viewedStoreMeal"
    }),
    orderId() {
      return this.$route.params.id;
    }
  },
  mounted() {
    this.initBag();
  },
  methods: {
    ...mapActions({
      refreshUpcomingOrders: "refreshUpcomingOrders"
    }),
    async initBag() {
      await this.refreshUpcomingOrders();
      const order = _.find(this.upcomingOrders, {
        id: parseInt(this.orderId)
      });

      if (!order) {
        return;
      }
      console.log(this.orders, order);

      this.clearAll();

      _.forEach(order.items, item => {
        const meal = this.getMeal(item.meal_id);
        if (!meal) {
          return;
        }

        let components = _.mapValues(
          _.groupBy(item.components, "meal_component_id"),
          choices => {
            return _.map(choices, "meal_component_option_id");
          }
        );

        let addons = _.map(item.addons, "meal_addon_id");

        for (let i = 0; i < item.quantity; i++) {
          this.addOne(meal, false, item.meal_size_id, components, addons);
        }
      });
    }
  }
};
</script>
