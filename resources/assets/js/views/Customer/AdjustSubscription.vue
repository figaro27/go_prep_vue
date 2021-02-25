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
      subscription: {},
      itemsSet: false
    };
  },
  computed: {
    ...mapGetters({
      subscriptions: "subscriptions",
      store: "viewedStore",
      bag: "bag",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      mealMixItems: "mealMixItems",
      context: "context",
      bagPickup: "bagPickup"
    }),
    subscriptionId() {
      return this.$route.params.id;
    }
  },
  mounted() {
    if (
      this.mealMixItems.hasOwnProperty("isRunningLazy") &&
      !this.mealMixItems.isRunningLazy &&
      !this.itemsSet
    ) {
      if (this.bag.items && this.bag.items.length === 0) {
        this.getSub();
      }
    }
  },
  watch: {
    mealMixItems: function() {
      if (
        this.mealMixItems.hasOwnProperty("isRunningLazy") &&
        !this.mealMixItems.isRunningLazy &&
        !this.itemsSet
      ) {
        if (this.bag.items && this.bag.items.length === 0) this.getSub();
      }
    }
  },
  methods: {
    ...mapActions(["refreshSubscriptions"]),
    ...mapMutations([
      "setBagPickup",
      "setBagPickupLocation",
      "setBagGratuityPercent",
      "setBagCustomGratuity",
      "setBagCoupon",
      "setBagSubscriptionInterval",
      "setBagSubscription",
      "setBagNotes",
      "setBagPublicNotes"
    ]),
    setSubscriptionCoupon() {
      axios
        .post("/api/me/findCouponById", {
          store_id: this.subscription.store_id,
          couponId: this.subscription.coupon_id
        })
        .then(resp => {
          if (resp.data) {
            this.setBagCoupon(resp.data);
          }
        });
    },
    getSub() {
      axios.get("/api/me/subscriptions/" + this.subscriptionId).then(resp => {
        this.subscription = resp.data;
        this.$route.params.subscription = this.subscription;
        this.setBagSubscription(resp.data);
        this.initBag();
        if (this.subscription.coupon_id) {
          this.setSubscriptionCoupon();
        }
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
      this.setBagPickupLocation(subscription.pickup_location_id);
      this.setBagGratuityPercent("custom");
      this.setBagCustomGratuity(subscription.gratuity);
      this.setBagNotes(this.subscription.notes);
      this.setBagPublicNotes(this.subscription.publicNotes);
      let transferType = this.bagPickup ? "pickup" : "delivery";

      let interval = null;
      switch (subscription.intervalCount) {
        case 1:
          interval = "week";
          break;
        case 2:
          interval = "biweek";
          break;
        case 4:
          interval = "month";
          break;
      }
      this.setBagSubscriptionInterval(interval);

      let stop = false;

      let delivery_days = this.allDeliveryDays;

      if (subscription.meal_package_items) {
        _.forEach(subscription.meal_package_items, pkgItem => {
          let meal_package_id = pkgItem.meal_package_id;
          let meal_package = this.getMealPackage(meal_package_id);
          meal_package.price = pkgItem.price;
          meal_package.mappingId = pkgItem.mappingId;

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
              const meal = { ...this.getMeal(item.meal_id) };
              meal.meal_size_id = item.meal_size_id;
              meal.quantity = item.quantity / pkgItem.quantity;
              meal.special_instructions = item.special_instructions;
              meal.item_id = item.item_id;
              meal.price = item.price / item.quantity;
              meal.added_price = item.added_price / item.quantity;

              if (pkgItem.meal_package_size && index !== null) {
                meal_package.sizes[index].meals.push(meal);
                meal_package.sizes[index].price = pkgItem.price;
              } else {
                meal_package.meals.push(meal);
              }
            }
          });
          if (this.store.modules.multipleDeliveryDays) {
            let deliveryDay = delivery_days.find(day => {
              return (
                day.day_friendly == pkgItem.delivery_date &&
                day.type == transferType
              );
            });
            meal_package.dday = deliveryDay;
          }

          meal_package.adjustSubscription = true;
          meal_package.customTitle = pkgItem.customTitle;
          meal_package.category_id = pkgItem.category_id;

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
            let deliveryDay = delivery_days.find(day => {
              return (
                day.day_friendly ==
                  moment(item.delivery_date.date).format("YYYY-MM-DD") &&
                day.type == transferType
              );
            });
            meal.delivery_day = deliveryDay;
          }

          meal.category_id = item.category_id;
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
      this.itemsSet = true;
    }
  }
};
</script>
