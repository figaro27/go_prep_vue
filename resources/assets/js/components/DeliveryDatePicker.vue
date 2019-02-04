<template>
  <div class="delivery-date-picker mb-0">
    <div class="d-flex align-items-center">
      <div class="mr-2">Delivery dates:</div>

      <div class="flex-grow-1">
        <date-range-picker
          :value="value"
          @selected="val => onChange(val)"
          i18n="EN"></date-range-picker>
        <!--<v-select
          multiple
          v-model="delivery_dates"
          @change="val => onChange(val)"
          :options="deliveryDateOptions"
        ></v-select>-->
      </div>
    </div>
  </div>
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

      val = {...val};
      if(val.start)
        val.start = moment(val.start).subtract(1, 'day').startOf('date');
      if(val.end)
        val.end = moment(val.end).subtract(1, 'day').endOf('date');

      //val = [val.start, val.end];
      this.$emit("input", val);

      /*
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
      this.$emit("input", val);*/
    },
    onDateSelected(val) {

    },
    update(val) {}
  }
};
</script>