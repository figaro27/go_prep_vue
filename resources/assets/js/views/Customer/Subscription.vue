<template>
  <customer-menu
    :subscription-id="$route.params.id"
    :weeklySubscriptionValue="1"
    :pickup="pickup"
  ></customer-menu>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import format from "../../lib/format.js";
import Spinner from "../../components/Spinner";
import MenuBag from "../../mixins/menuBag";
import CustomerMenu from "./Menu";

export default {
  components: {
    Spinner,
    CustomerMenu
  },
  mixins: [MenuBag],
  data() {
    return {
      isLoading: false,
      pickup: null
    };
  },
  computed: {
    ...mapGetters({
      subscriptions: "subscriptions",
      store: "store",
      bag: "bag",
      getMeal: "viewedStoreMeal"
    }),
    subscriptionId() {
      return this.$route.params.id;
    }
  },
  mounted() {
    if (!this.$route.params.id) {
      console.log("test");
    }
    setTimeout(() => {
      this.initBag();
    }, 3000);
    // if (this.$route.params.subscriptionOnly) {
    //   setTimeout(() => {
    //     this.initBag();
    //   }, 3000);
    // } else {
    //   this.initBag();
    // }
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    async initBag() {
      this.clearAll();
      await this.refreshSubscriptions();
      const subscription = _.find(this.subscriptions, {
        id: parseInt(this.subscriptionId)
      });

      if (!subscription) {
        return;
      }

      // Setting pickup here
      this.pickup = subscription.pickup;

      _.forEach(subscription.items, item => {
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

        for (let i = 0; i < item.quantity; i++) {
          this.addOne(
            meal,
            false,
            item.meal_size_id,
            components,
            addons,
            special_instructions
          );
        }
      });
    }
  }
};
</script>
