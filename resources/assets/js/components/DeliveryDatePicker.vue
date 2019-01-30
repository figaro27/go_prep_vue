<template>
  <b-form-group class="delivery-date-picker mb-0">
    <div class="d-flex align-items-center">
      <div class="mr-2">Delivery dates:</div>

      <div>
        <v-select
          multiple
          v-model="delivery_dates"
          @change="val => onChange(val)"
          :options="deliveryDateOptions"
        ></v-select>
      </div>
    </div>
  </b-form-group>
</template>

<style lang="scss">
.delivery-date-picker {
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
import units from "../data/units";
import format from "../lib/format";

export default {
  props: {
    value: {}
    /*options: {
      default: {
        //saveButton: false
      }
    },*/
  },
  data() {
    return {
      delivery_dates: ["All"],
      changed: false
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      storeSettings: "storeSettings"
    }),
    deliveryDateOptions() {
      if (!this.orders.length) {
        return ["All"];
      }

      let grouped = [];
      this.orders.forEach(order => {
        if (!_.includes(grouped, order.delivery_date)) {
          grouped.push(order.delivery_date);
        }
      });
      //grouped.push("All");
      this.deliveryDate = grouped[0];
      return grouped;
    },
    selected() {
      return this.deliveryDates;
    },
    numDeliveryDates() {
      return this.storeSettings.view_delivery_days;
    }
  },
  watch: {
    delivery_dates(val) {
      this.onChange(val);
    },
    deliveryDateOptions(val, oldVal) {
      if (
        !this.changed &&
        val[0] !== "All" &&
        this.numDeliveryDates &&
        oldVal[0] === "All"
      ) {
        let selected = val.splice(1, this.numDeliveryDates);

        this.$nextTick(() => {
          this.delivery_dates = selected;
          this.changed = true;
          this.$forceUpdate();
        });
      }
    }
  },
  created() {},
  mounted() {
    if (this.orders.length && this.numDeliveryDates) {
      const startIndex = this.deliveryDateOptions[0] === "All" ? 1 : 0;

      this.$nextTick(() => {
        let selected = this.deliveryDateOptions.splice(
          startIndex,
          this.numDeliveryDates
        );
        this.delivery_dates = selected;
        this.changed = true;
        this.$forceUpdate();
      });
    }
  },
  methods: {
    ...mapActions([]),
    onChange(val) {
      this.changed = true;

      if (!val.length) {
        val = ["All"];
        this.delivery_dates = val;
      } else if (val.length >= 2 && val[0] === "All") {
        val = _.filter(val, date => {
          return date !== "All";
        });
        this.delivery_dates = val;
      }

      if (!val.length) {
        val = null;
      }
      this.$emit("input", val);
    },
    update(val) {}
  }
};
</script>