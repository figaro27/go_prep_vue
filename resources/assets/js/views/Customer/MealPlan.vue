<template>
  <customer-menu :subscription-id="$route.params.id"></customer-menu>
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
      isLoading: false
    };
  },
  computed: {
    ...mapGetters(["subscriptions", "store", "bag", "viewedStoreMeal"]),
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
      await this.refreshSubscriptions();
      const subscription = _.find(this.subscriptions, {
        id: parseInt(this.subscriptionId)
      });

      if (!subscription) {
        return;
      }
      console.log(this.subscriptions, subscription);

      this.clearAll();

      _.forEach(subscription.items, item => {
        const meal = this.viewedStoreMeal(item.meal_id);
        if (!meal) {
          return;
        }

        let components = _.mapValues(
          _.keyBy(item.components, "meal_component_id"),
          component => {
            return component.meal_component_option_id;
          }
        );

        let addons = _.map(item.addons, "meal_addon_id");

        this.addOne(meal, false, item.meal_size_id, components, addons);
      });
    }
  }
};
</script>
