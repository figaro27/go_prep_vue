<template>
  <div class="delivery-date-picker mb-0 flex-grow-0">
    <div class="d-flex flex-wrap align-items-center">
      <div v-if="!hideDateLabel">
        <div
          class="mr-sm-2 flex-grow-0"
          v-if="!storeSettings.allowPickup && !orderDate && !regularDate"
        >
          Delivery Dates:
        </div>
        <div
          class="mr-sm-2 flex-grow-0"
          v-if="storeSettings.allowPickup && !orderDate"
        >
          Delivery / Pickup Dates:
        </div>
        <div class="mr-sm-2 flex-grow-0" v-if="orderDate">
          Payment Dates:
        </div>
        <div class="mr-sm-2 flex-grow-0" v-if="regularDate">
          Dates:
        </div>
      </div>
      <div class="flex-grow-0">
        <date-range-picker
          ref="picker"
          @selected="val => onChange(val)"
          :class="pickerClasses"
          i18n="EN"
          :righttoleft="rtl ? 'true' : 'false'"
          :compact="'false'"
        ></date-range-picker>
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
    hideDateLabel: {
      default: false
    },
    regularDate: {
      default: false
    },
    orderDate: {
      default: false
    },
    value: {},
    /*options: {
      default: {
        //saveButton: false
      }
    },*/
    rtl: {
      type: Boolean,
      default: false
    }
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
    },
    delivery_days() {
      return this.storeSettings.delivery_days || [];
    },
    pickerClasses() {
      return {
        "highlight-mon": this.delivery_days.includes("mon"),
        "highlight-tue": this.delivery_days.includes("tue"),
        "highlight-wed": this.delivery_days.includes("wed"),
        "highlight-thu": this.delivery_days.includes("thu"),
        "highlight-fri": this.delivery_days.includes("fri"),
        "highlight-sat": this.delivery_days.includes("sat"),
        "highlight-sun": this.delivery_days.includes("sun")
      };
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
    ...mapGetters({
      getStoreSetting: "storeSetting"
    }),
    clearDates() {
      this.$refs.picker.dateRange.from = null;
      this.$refs.picker.dateRange.to = null;
      this.$refs.picker.dateRange.start = null;
      this.$refs.picker.dateRange.end = null;
    },
    setInitialRange() {
      return; // Disabled
      if (
        _.isEmpty(this.$refs.picker.dateRange) &&
        this.orders.length &&
        this.nextDeliveryDates &&
        this.numDeliveryDates
      ) {
        this.$nextTick(() => {
          this.$refs.picker.dateRange = {
            start: moment
              .utc()
              .startOf("date")
              .toDate(),
            end: moment
              .utc(this.nextDeliveryDates[this.numDeliveryDates - 1].date)
              .endOf("date")
          };
          this.$forceUpdate();
        });
      }
    },
    onChange(val) {
      this.changed = true;
      val = { ...val };
      if (val.start)
        val.start = moment
          .utc(val.start)
          .subtract(1, "day")
          .startOf("date");
      if (val.end)
        val.end = moment
          .utc(val.end)
          .subtract(1, "day")
          .endOf("date");

      //val = [val.start, val.end];
      this.$emit("input", val);
      this.$emit("change", val);

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
