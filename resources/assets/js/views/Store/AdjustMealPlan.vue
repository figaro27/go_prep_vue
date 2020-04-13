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

      // axios.get("/api/me/subscription_bag/" + subscription.id).then(resp => {
      //   if (resp.data && resp.data.subscription_bags) {
      //     this.subscription_bags = resp.data.subscription_bags;

      //     // Don't load from SubscriptionBag if meals have been replaced in the subscription - meals aren't replacing in the stored object currently
      //     if (
      //       !subscription.mealsReplaced &&
      //       this.subscription_bags.length > 0
      //     ) {
      //       this.subscription_bags.forEach(item => {
      //         this.addOneFromAdjust(item);
      //       });
      //     } else {
      //       _.forEach(subscription.items, item => {
      //         const meal = this.getMeal(item.meal_id);
      //         if (!meal) {
      //           return;
      //         }

      //         let components = _.mapValues(
      //           _.groupBy(item.components, "meal_component_id"),
      //           choices => {
      //             return _.map(choices, "meal_component_option_id");
      //           }
      //         );

      //         let addons = _.map(item.addons, "meal_addon_id");

      //         let special_instructions = item.special_instructions;

      //         for (let i = 0; i < item.quantity; i++) {
      //           this.addOne(
      //             meal,
      //             false,
      //             item.meal_size_id,
      //             components,
      //             addons,
      //             special_instructions
      //           );
      //         }
      //       });
      //     }
      //   }
      // });

      if (subscription.meal_package_items) {
        _.forEach(subscription.meal_package_items, pkgItem => {
          let meal_package_id = pkgItem.meal_package_id;
          let meal_package = this.getMealPackage(meal_package_id);
          meal_package.price = pkgItem.price;

          // Adding meals to meal package
          meal_package.meals = [];
          let index = null;
          if (pkgItem.meal_package_size) {
            _.forEach(meal_package.sizes, (size, i) => {
              if (pkgItem.meal_package_size_id) {
                if (size.id === pkgItem.meal_package_size_id) {
                  index = i;
                }
              } else {
                if (size.id === pkgItem.meal_package_size.id) {
                  index = i;
                }
              }
            });
          }
          if (index !== null) {
            meal_package.sizes[index].meals = [];
          }

          _.forEach(subscription.items, item => {
            if (item.meal_package_subscription_id === pkgItem.id) {
              const meal = this.getMeal(item.meal_id);
              meal.meal_size_id = item.meal_size_id;
              meal.quantity = item.quantity;
              meal.special_instructions = item.special_instructions;

              if (pkgItem.meal_package_size && index !== null) {
                meal_package.sizes[index].meals.push(meal);
                meal_package.sizes[index].price = pkgItem.price;
              } else {
                meal_package.meals.push(meal);
              }
            }
          });
          for (let i = 0; i < pkgItem.quantity; i++) {
            this.addOne(meal_package, true, pkgItem.meal_package_size_id);
          }
        });
      }

      _.forEach(subscription.items, item => {
        console.log(item);
        if (!item.meal_package_subscription_id) {
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

          meal.price = item.price;

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
        }
      });
    }
  }
};
</script>
