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
      :lineItemOrders="lineItemOrders"
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
      order: [],
      inSub: null,
      deliveryDay: null,
      transferTime: null,
      pickup: null,
      isLoading: false,
      order_bags: [],
      lineItemOrders: []
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
    }
    // inSub() {
    //   return this.order.subscription_id ? 1 : 0;
    // },
    // deliveryDay() {
    //   return moment(this.order.delivery_date).format("YYYY-MM-DD 00:00:00");
    // },
    // transferTime() {
    //   return this.order.transferTime;
    // },
    // pickup() {
    //   return this.order.pickup;
    // }
  },
  mounted() {
    if (this.orderId === undefined) {
      this.$router.push({ path: "/store/orders" });
    }
    this.initOrder();
    this.initBag();
  },
  methods: {
    ...mapActions({
      refreshUpcomingOrders: "refreshUpcomingOrders"
    }),
    initOrder() {
      let order = _.find(this.upcomingOrders, order => {
        return order.id === this.orderId;
      });
      this.order = order;
      this.inSub = this.order.subscription_id ? 1 : 0;
      this.deliveryDay = moment(this.order.delivery_date).format(
        "YYYY-MM-DD 00:00:00"
      );
      this.transferTime = this.order.transferTime;
      this.pickup = this.order.pickup;
    },
    async initBag() {
      // await this.refreshUpcomingOrders();
      // const order = _.find(this.upcomingOrders, {
      //   id: parseInt(this.orderId)
      // });

      // if (!order) {
      //   return;
      // }

      this.clearAll();

      axios.get("/api/me/order_bag/" + this.order.id).then(resp => {
        if (resp.data && resp.data.order_bags) {
          this.order_bags = resp.data.order_bags;

          if (this.order_bags) {
            this.order_bags.forEach(item => {
              this.addOneFromAdjust(item);
            });
          }
        }
      });

      axios.get("/api/me/getLineItemOrders/" + this.order.id).then(resp => {
        resp.data.forEach(lineItemOrder => {
          this.lineItemOrders.push(lineItemOrder);
        });
      });

      /*
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

      /*_.forEach(this.order.items, item => {
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
      });*/
    }
  }
};
</script>
