<template>
  <div class="delivery-date-picker mb-0">
    <div class="d-flex align-items-center">
      <div class="mr-2" v-if="!storeSettings.allowPickup">Delivery Dates:</div>
      <div class="mr-2" v-if="storeSettings.allowPickup">Delivery / Pickup Dates:</div>

      <div class="flex-grow-1">
        <date-range-picker ref="picker" @selected="val => onChange(val)" i18n="EN"></date-range-picker>
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
      storeSettings: "storeSettings",
      nextDeliveryDates: "storeNextDeliveryDates"
    }),
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
    orders(val) {
      if (val.length) {
        this.$forceUpdate();
      }
    },
    nextDeliveryDates(val) {
      if (val.length) {
        this.$forceUpdate();
      }
    },
    numDeliveryDates(val) {
      if (val) {
        this.$forceUpdate();
      }
    }
  },
  created() {
    this.$nextTick(() => {
      this.setInitialRange();
    });
  },
  mounted() {
    this.$nextTick(() => {
      this.setInitialRange();
    });
  },
  updated() {
    this.setInitialRange();
  },
  methods: {
    ...mapActions([]),
    setInitialRange() {
      if (
        _.isEmpty(this.$refs.picker.dateRange) &&
        this.orders.length &&
        this.nextDeliveryDates &&
        this.numDeliveryDates
      ) {
        this.$nextTick(() => {
          this.$refs.picker.dateRange = {
            start: moment()
              .startOf("date")
              .toDate(),
            end: moment(this.nextDeliveryDates[this.numDeliveryDates - 1].date)
              .endOf("date")
              .toDate()
          };
          this.$forceUpdate();
        });
      }
    },
    onChange(val) {
      this.changed = true;

      val = { ...val };
      if (val.start)
        val.start = moment(val.start)
          .subtract(1, "day")
          .startOf("date");
      if (val.end)
        val.end = moment(val.end)
          .subtract(1, "day")
          .endOf("date");

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
    onDateSelected(val) {},
    update(val) {}
  }
};
</script>