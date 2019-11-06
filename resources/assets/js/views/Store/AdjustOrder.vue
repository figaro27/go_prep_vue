<template>
  <div>
    <!-- <Spinner v-if="loading" /> -->
    <customer-menu
      :adjustOrder="true"
      :orderId="orderId"
      :storeView="true"
      :deliveryDay="deliveryDay"
      :transferTime="transferTime"
      :pickup="pickup"
      :order="order"
      :inSub="inSub"
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
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    orderId() {
      return this.$route.params.orderId;
    },
    order() {
      let order = _.find(this.upcomingOrders, order => {
        return order.id === this.orderId;
      });
      return order;
    },
    inSub() {
      return this.order.subscription_id ? 1 : 0;
    },
    deliveryDay() {
      return moment(this.order.delivery_date).format("YYYY-MM-DD 00:00:00");
    },
    transferTime() {
      return this.order.transferTime;
    },
    pickup() {
      return this.order.pickup;
    }
  },
  mounted() {
    if (this.orderId === undefined) {
      this.$router.push({ path: "/store/orders" });
    }
    this.initBag();
  },
  methods: {
    ...mapActions({
      refreshUpcomingOrders: "refreshUpcomingOrders"
    }),
    async initBag() {
      // await this.refreshUpcomingOrders();
      // const order = _.find(this.upcomingOrders, {
      //   id: parseInt(this.orderId)
      // });

      // if (!order) {
      //   return;
      // }
      // console.log(this.orders, order);

      this.clearAll();

      /*console.log('order', this.order)
      
      if (this.order.meal_package_items) {
        _.forEach(this.order.meal_package_items, item => {
            let meal_package_id = item.meal_package_id
            let meal_package = this.getMealPackage(meal_package_id)
            
            if (meal_package) {
                console.log('meal_package', meal_package)
                let size = item.meal_package_size
            }
        })
      }*/

      _.forEach(this.order.items, item => {
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

        let special_instructions = item.special_instructions;

        let free = item.free;

        for (let i = 0; i < item.quantity; i++) {
          this.addOne(
            meal,
            false,
            item.meal_size_id,
            components,
            addons,
            special_instructions,
            free
          );
        }
      });
    }
  }
};
</script>
