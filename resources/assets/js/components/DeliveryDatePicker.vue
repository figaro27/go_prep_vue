<template>
  <b-form-group label="Delivery dates" class="delivery-date-picker mb-0">
    <v-select
      multiple
      v-model="delivery_dates"
      @change="val => onChange(val)"
      :options="deliveryDateOptions"
    ></v-select>
  </b-form-group>
</template>

<style lang="scss" scoped>
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
      delivery_dates: ["All"]
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      storeSettings: "storeSettings",
    }),
    deliveryDateOptions() {
      let grouped = [];
      this.orders.forEach(order => {
        if (!_.includes(grouped, order.delivery_date)) {
          grouped.push(order.delivery_date);
        }
      });
      grouped.push("All");
      this.deliveryDate = grouped[0];
      return grouped;
    },
    selected() {
      return this.deliveryDates;
    }
  },
  watch: {
    delivery_dates(val) {
      this.onChange(val);
    },
    deliveryDateOptions(val, oldVal) {
      if (val[0] !== 'All' && this.storeSettings.view_delivery_days && oldVal[0] === 'All') {
        let selected = val.splice(1, this.storeSettings.view_delivery_days);
        
        this.$nextTick(() => {
          this.delivery_dates = selected;
        })
      }
    }
  },
  created() {},
  mounted() {},
  methods: {
    ...mapActions([]),
    onChange(val) {
      if (!val.length) {
        val = ["All"];
        this.delivery_dates = val;
      } else if (val.length >= 2 && val[0] === "All") {
        val = _.filter(val, date => {
          return date !== "All";
        });
        this.delivery_dates = val;
      }
      this.$emit("input", val);
    },
    update(val) {}
  }
};
</script>