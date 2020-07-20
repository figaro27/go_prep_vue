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
import { mapGetters, mapActions, mapMutations } from "vuex";
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
      order_bags: [],
      lineItemOrders: []
    };
  },
  computed: {
    ...mapGetters({
      upcomingOrders: "storeUpcomingOrders",
      store: "viewedStore",
      bag: "bag",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage"
    }),
    orderId() {
      return this.$route.params.orderId;
    },
    order() {
      return this.$route.params.order;
    },
    inSub() {
      return this.order.subscription_id ? 1 : 0;
    },
    deliveryDay() {
      return this.order
        ? moment(this.order.delivery_date).format("YYYY-MM-DD 00:00:00")
        : null;
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
    if (!this.$route.params.backFromBagPage) {
      this.initBag();
    }
  },
  methods: {
    ...mapActions({
      refreshUpcomingOrders: "refreshUpcomingOrders"
    }),
    ...mapMutations(["setBagStaffMember", "setBagPickup"]),
    async initBag() {
      // await this.refreshUpcomingOrders();
      // const order = _.find(this.upcomingOrders, {
      //   id: parseInt(this.orderId)
      // });

      // if (!order) {
      //   return;
      // }
      this.setBagStaffMember(this.order.staff_id);
      this.setBagPickup(this.order.pickup);
      this.clearAll();

      // axios.get("/api/me/order_bag/" + this.order.id).then(resp => {
      //   if (resp.data && resp.data.order_bags) {
      //     this.order_bags = resp.data.order_bags;

      //     if (this.order_bags) {
      //       this.order_bags.forEach(item => {
      //         this.addOneFromAdjust(item);
      //       });
      //     }
      //   }
      // });

      axios.get("/api/me/getLineItemOrders/" + this.order.id).then(resp => {
        resp.data.forEach(lineItemOrder => {
          this.lineItemOrders.push(lineItemOrder);
        });
      });

      if (this.order.meal_package_items) {
        _.forEach(this.order.meal_package_items, pkgItem => {
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

          _.forEach(this.order.items, item => {
            if (item.meal_package_order_id === pkgItem.id && !item.hidden) {
              const meal = this.getMeal(item.meal_id);
              meal.meal_size_id = item.meal_size_id;
              meal.quantity = item.quantity / pkgItem.quantity;
              meal.special_instructions = item.special_instructions;

              if (pkgItem.meal_package_size && index !== null) {
                meal_package.sizes[index].meals.push(meal);
                meal_package.sizes[index].price = pkgItem.price;
              } else {
                meal_package.meals.push(meal);
              }
            }
          });

          meal_package.title = pkgItem.customTitle
            ? pkgItem.customTitle
            : meal_package.title;

          // if (pkgItem.customSize) {
          //   let sizeId = pkgItem.meal_package_size_id;
          //   let size = meal_package.sizes.find(size => {
          //     return (size.id = sizeId);
          //   });
          //   size.title = pkgItem.customSize;
          // }

          if (this.store.modules.multipleDeliveryDays) {
            let delivery_day = this.store.delivery_days.find(day => {
              return day.day == moment(pkgItem.delivery_date).day();
            });
            meal_package.delivery_day = delivery_day;
          }

          meal_package.adjustOrder = true;
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

      _.forEach(this.order.items, item => {
        if (!item.meal_package_order_id && !item.hidden && !item.attached) {
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
          }

          meal.customTitle = item.customTitle;
          meal.customSize = item.customSize;

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

      _.forEach(this.order.purchased_gift_cards, item => {
        item.price = item.amount;
        item.image = { url_thumb: item.image };
        item.gift_card = true;

        this.addOne(item, null);
      });
    }
  }
};
</script>
