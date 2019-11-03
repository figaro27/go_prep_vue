<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-8 offset-sm-2 col-md-6 offset-md-3">
        <div class="card">
          <div class="card-body">
            <h3>Checkout</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss"></style>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import { Switch as cSwitch } from "@coreui/vue";
export default {
  components: {
    cSwitch
  },
  data() {
    return {};
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeSetting: "viewedStoreSetting",
      total: "bagQuantity",
      bag: "bagItems",
      hasMeal: "bagHasMeal",
      totalBagPrice: "totalBagPrice"
    }),
    storeSettings() {
      return this.store.settings;
    },
    minimum() {
      return this.storeSetting("minimum", 1);
    },
    remainingMeals() {
      return this.minimum - this.total;
    },
    singOrPlural() {
      if (this.remainingMeals > 1) {
        return "items";
      }
      return "meal";
    },
    singOrPluralTotal() {
      if (this.total > 1) {
        return "items";
      }
      return "item";
    },
    deliveryPlanText() {
      if (this.deliveryPlan) return "Prepared Weekly";
      else return "One Time Order";
    },
    totalBagPriceAfterDiscount() {
      return (
        (this.totalBagPrice * (100 - this.mealPlanDiscountPercent)) /
        100
      ).toFixed(2);
    },
    mealPlanDiscountAmount() {
      return (
        this.totalBagPrice -
        (this.totalBagPrice * (100 - this.mealPlanDiscountPercent)) / 100
      ).toFixed(2);
    },
    deliveryDateOptions() {
      return this.storeSetting("next_delivery_dates", []).map(date => {
        return {
          value: date.date,
          text: moment(date.date).format("dddd MMM Do")
        };
      });
    }
  },
  mounted() {},
  methods: {
    checkout() {
      axios
        .post("/api/bag/checkout", {
          bag: this.bag,
          pickup: this.pickup,
          delivery_day: this.deliveryDay
        })
        .then(resp => {})
        .catch(e => {})
        .finally(() => {});
    }
  }
};
</script>
