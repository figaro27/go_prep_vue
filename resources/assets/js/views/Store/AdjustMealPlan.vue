<template>
  <customer-menu
    :subscription-id="$route.params.id"
    :storeView="true"
    :adjustMealPlan="true"
    :weeklySubscriptionValue="1"
    :pickup="pickup"
  ></customer-menu>
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
      isLoading: false,
      pickup: null,
      subscription_bags: []
    };
  },
  computed: {
    ...mapGetters({
      subscriptions: "storeSubscriptions",
      store: "store",
      bag: "bag",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    subscriptionId() {
      return this.$route.params.id;
    }
  },
  mounted() {
    this.initBag();
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    async initBag() {
      const subscription = _.find(this.subscriptions, {
        id: parseInt(this.subscriptionId)
      });

      if (!subscription) {
        return;
      }

      // Setting pickup here
      this.pickup = subscription.pickup;

      this.clearAll();
      let stop = false;

      axios.get("/api/me/subscription_bag/" + subscription.id).then(resp => {
        if (resp.data && resp.data.subscription_bags) {
          this.subscription_bags = resp.data.subscription_bags;

          if (this.subscription_bags.length > 0) {
            // Until SubscriptionBag gets updated when meals are replaced, don't load anything when meal packages are involved.
            if (subscription.mealsReplaced) {
              this.subscription_bags.forEach(item => {
                if (item.meal_package) {
                  stop = true;
                }
              });
            }
            if (stop) {
              return;
            }
            this.subscription_bags.forEach(item => {
              this.addOneFromAdjust(item);
            });
          } else {
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
      });
    }
  }
};
</script>
