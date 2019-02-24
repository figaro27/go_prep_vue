<template>
  <div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Orders Summary</h4>
            <div class="report-date-picker">
              <delivery-date-picker v-model="delivery_dates.orders_by_customer"></delivery-date-picker>
            </div>
            <p class="mt-4 center-text">Shows how to bag up your meals for each customer.</p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('orders_by_customer', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >Print</button>
              </div>
              <div class="col-md-6">
                <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                  <b-dropdown-item @click="exportData('orders_by_customer', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('orders_by_customer', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('orders_by_customer', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <Spinner v-if="isLoading"/>
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Production</h4>
            <div class="report-date-picker">
              <delivery-date-picker v-model="delivery_dates.meal_orders" :rtl="true"></delivery-date-picker>
            </div>
            <p class="mt-4 center-text">Shows how many of each meal to make based on your orders.</p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('meal_orders', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >Print</button>
              </div>
              <div class="col-md-6">
                <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                  <b-dropdown-item @click="exportData('meal_orders', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('meal_orders', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('meal_orders', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Packing Slips</h4>
            <div class="report-date-picker">
              <delivery-date-picker v-model="delivery_dates.packing_slips"></delivery-date-picker>
            </div>
            <p
              class="mt-4 center-text"
            >Show packing slips or order summaries to include in your bag to the customers.</p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('packing_slips', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >Print</button>
              </div>
              <div class="col-md-6">
                <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                  <b-dropdown-item @click="exportData('packing_slips', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('packing_slips', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('packing_slips', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-4">
            <h4 class="center-text mb-4">Ingredients</h4>
            <div class="report-date-picker">
              <delivery-date-picker v-model="delivery_dates.ingredient_quantities" :rtl="true"></delivery-date-picker>
            </div>
            <p
              class="mt-4 center-text"
            >Shows how much of each ingredient is needed based on your orders.</p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('ingredient_quantities', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >Print</button>
              </div>
              <div class="col-md-6">
                <b-dropdown variant="warning" class="center mt-2" right text="Export as">
                  <b-dropdown-item @click="exportData('ingredient_quantities', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('ingredient_quantities', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('ingredient_quantities', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import vSelect from "vue-select";
import Spinner from "../../components/Spinner";
import checkDateRange from "../../mixins/deliveryDates";

export default {
  components: {
    vSelect,
    Spinner
  },
  data() {
    return {
      delivery_dates: {
        meal_quantities: [],
        meal_orders: [],
        ingredient_quantities: [],
        orders_by_customer: [],
        packing_slips: []
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      isLoading: "isLoading"
    }),
    selected() {
      return this.deliveryDates;
    },
    deliveryDates() {
      let grouped = [];
      this.orders.forEach(order => {
        if (!_.includes(grouped, order.delivery_date)) {
          grouped.push(order.delivery_date);
        }
      });
      this.deliveryDate = grouped[0];
      return grouped;
    }
  },
  mixins: [checkDateRange],
  mounted() {},
  methods: {
    async print(report, format = "pdf") {
      let params = {};

      let dates = this.delivery_dates[report];
      if (dates.start && dates.end) {
        params.delivery_dates = {
          from: dates.start,
          to: dates.end
        };

        const warning = this.checkDateRange({ ...dates });
        if (warning) {
          try {
            let dialog = await this.$dialog.confirm(
              "You have selected a date range which includes delivery days which haven't passe" +
                "d their cutoff period. This means new orders can still come in for those days. Continue?"
            );
            dialog.close();
          } catch (e) {
            return;
          }
        }
      }

      axios
        .get(`/api/me/print/${report}/${format}`, {
          params
        })
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            win.addEventListener(
              "load",
              () => {
                win.print();
              },
              false
            );
          }
        })
        .catch(err => {
          this.$toastr.e(
            "Please confirm that data exists for the selected date range.",
            "Failed to print report."
          );
        })
        .finally(() => {
          this.loading = false;
        });
    },
    async exportData(report, format = "pdf", print = false) {
      let dates = this.delivery_dates[report];
      if (dates.start && dates.end) {
        const warning = this.checkDateRange({ ...dates });
        if (warning) {
          try {
            let dialog = await this.$dialog.confirm(
              "You have selected a date range which includes delivery days which haven't passe" +
                "d their cutoff period. This means new orders can still come in for those days. Continue?"
            );
            dialog.close();
          } catch (e) {
            return;
          }
        }
      }

      axios
        .get(`/api/me/print/${report}/${format}`)
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            if (print) {
              win.addEventListener(
                "load",
                () => {
                  win.print();
                },
                false
              );
            }
          }
        })
        .catch(err => {})
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>