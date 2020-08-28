<template>
  <customer-menu
    :subscription-id="$route.params.id"
    :weeklySubscriptionValue="1"
    :pickup="pickup"
    :subscription="subscription"
  ></customer-menu>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
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
      pickup: null,
      subscription_bags: [],
      subscription: {}
    };
  },
  computed: {
    ...mapGetters({
      subscriptions: "subscriptions",
      store: "viewedStore",
      bag: "bag",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      mealMixItems: "mealMixItems"
    }),
    subscriptionId() {
      return this.$route.params.id;
    }
  },
  mounted() {
    if (!this.mealMixItems.isRunningLazy) {
      if (this.bag.items && this.bag.items.length === 0) {
        this.getSub();
      }
    }
  },
  watch: {
    mealMixItems: function() {
      if (!this.mealMixItems.isRunningLazy) {
        if (this.bag.items && this.bag.items.length === 0) this.getSub();
      }
    }
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    ...mapMutations(["setBagPickup"]),
    getSub() {
      axios.get("/api/me/subscriptions/" + this.subscriptionId).then(resp => {
        this.subscription = resp.data;
        this.$route.params.subscription = this.subscription;
        this.initBag();
      });
    },
    async initBag() {
      this.clearAll();
      // await this.refreshSubscriptions();
      // const subscription = _.find(this.subscriptions, {
      //   id: parseInt(this.subscriptionId)
      // });

      let subscription = this.subscription;

      if (!subscription) {
        return;
      }
      // Setting pickup here
      this.pickup = subscription.pickup;
      this.setBagPickup(subscription.pickup);

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
          if (this.store.modules.multipleDeliveryDays) {
            let delivery_day = this.store.delivery_days.find(day => {
              return day.day == moment(pkgItem.delivery_date).day();
            });
            meal_package.delivery_day = delivery_day;
            meal_package.delivery_day.day_friendly = pkgItem.delivery_date;
          }
          meal_package.customTitle = pkgItem.customTitle;

          for (let i = 0; i < pkgItem.quantity; i++) {
            this.addOne(
              meal_package,
              true,
              pkgItem.meal_package_size_id,
              null,
              null,
              null,
              null,
              pkgItem
            );
          }
        });
      }

      _.forEach(subscription.items, item => {
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

          meal.price = item.price / item.quantity;

          if (this.store.modules.multipleDeliveryDays) {
            let delivery_day = this.store.delivery_days.find(day => {
              return day.day == moment(item.delivery_date.date).day();
            });

            meal.delivery_day = delivery_day;
            meal.delivery_day.day_friendly = item.delivery_date.date;
          }

          for (let i = 0; i < item.quantity; i++) {
            this.addOne(
              meal,
              false,
              item.meal_size_id,
              components,
              addons,
              special_instructions,
              free,
              item
            );
          }
        }
      });
    }
  }
};
</script>
