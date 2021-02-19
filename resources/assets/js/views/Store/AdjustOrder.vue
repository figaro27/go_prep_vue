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
      ref="customerMenu"
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
      lineItemOrders: [],
      firstDeliveryDay: null
    };
  },
  computed: {
    ...mapGetters({
      upcomingOrders: "storeUpcomingOrders",
      store: "viewedStore",
      bag: "bag",
      getMeal: "viewedStoreMeal",
      getMealPackage: "viewedStoreMealPackage",
      context: "context"
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
    ...mapMutations([
      "setBagStaffMember",
      "setBagPickup",
      "setBagPickupLocation",
      "setBagGratuityPercent",
      "setBagCustomGratuity"
    ]),
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
      this.setBagPickupLocation(this.order.pickup_location_id);
      this.setBagGratuityPercent("custom");
      this.setBagCustomGratuity(this.order.gratuity);
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

      let delivery_days = [];

      if (this.store.modules.multipleDeliveryDays && this.context == "store") {
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();
        let date = today.getDate();

        for (let i = 0; i < 30; i++) {
          let day = new Date(year, month, date + i);
          let multDD = { ...this.store.delivery_days[0] };
          multDD.day_friendly = moment(day).format("YYYY-MM-DD");
          delivery_days.push(multDD);
        }
      }

      axios.get("/api/me/getLineItemOrders/" + this.order.id).then(resp => {
        resp.data.forEach(lineItemOrder => {
          this.lineItemOrders.push(lineItemOrder);
        });
      });

      if (this.order.meal_package_items) {
        _.forEach(this.order.meal_package_items, (pkgItem, pkgItemIndex) => {
          let meal_package_id = pkgItem.meal_package_id;
          let meal_package = { ...this.getMealPackage(meal_package_id) };
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

          _.forEach(this.order.items, (item, index) => {
            if (item.meal_package_order_id === pkgItem.id && !item.hidden) {
              const meal = { ...this.getMeal(item.meal_id) };
              meal.meal_size_id = item.meal_size_id;
              meal.price = item.price;
              meal.quantity = item.quantity / pkgItem.quantity;
              meal.special_instructions = item.special_instructions;
              meal.added_price = item.added_price / item.quantity;
              meal.item_id = item.item_id;

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

          if (pkgItem.customSize) {
            let sizeId = pkgItem.meal_package_size_id;
            let size = meal_package.sizes.find(size => {
              return size.id === sizeId;
            });
            size.title = pkgItem.customSize;
          }

          if (this.store.modules.multipleDeliveryDays) {
            // let deliveryDay = this.store.delivery_days[0];
            // deliveryDay.day_friendly = moment(pkgItem.delivery_date).format("YYYY-MM-DD");
            // deliveryDay.day = moment(pkgItem.delivery_date).format("d")
            let deliveryDay = this.store.delivery_days.find(day => {
              return day.day === moment(pkgItem.delivery_date).format("d");
            });

            if (!deliveryDay) {
              deliveryDay = this.store.delivery_days[0];
            }

            deliveryDay.day_friendly = moment(pkgItem.delivery_date).format(
              "YYYY-MM-DD"
            );

            if (pkgItemIndex == 0) {
              this.$refs.customerMenu.changeDeliveryDay(deliveryDay);
            }
            meal_package.delivery_day = deliveryDay;
          }

          meal_package.adjustOrder = true;
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

      _.forEach(this.order.items, (item, index) => {
        if (!item.meal_package_order_id && !item.hidden && !item.attached) {
          const meal = { ...this.getMeal(item.meal_id) };
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
            // let deliveryDay = this.store.delivery_days[0];
            // deliveryDay.day_friendly = moment(item.delivery_date.date).format("YYYY-MM-DD");
            // deliveryDay.day = moment(item.delivery_date.date).format("d");
            let deliveryDay = this.store.delivery_days.find(day => {
              return day.day === moment(item.delivery_date.date).format("d");
            });

            if (!deliveryDay) {
              deliveryDay = this.store.delivery_days[0];
            }

            deliveryDay.day_friendly = moment(item.delivery_date.date).format(
              "YYYY-MM-DD"
            );
            if (index == 0) {
              this.$refs.customerMenu.changeDeliveryDay(deliveryDay);
            }
            meal.delivery_day = deliveryDay;
          }

          meal.customTitle = item.customTitle;
          meal.customSize = item.customSize;

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

      _.forEach(this.order.purchased_gift_cards, item => {
        item.price = item.amount;
        item.image = { url_thumb: item.image };
        item.gift_card = true;

        this.addOne(item, null);
      });

      if (this.store.modules.multipleDeliveryDays && this.selectedDeliveryDay) {
        this.$refs.customerMenu.changeDeliveryDay(this.selectedDeliveryDay);
      }
    }
  }
};
</script>
