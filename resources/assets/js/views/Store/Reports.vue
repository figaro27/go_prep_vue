<template>
  <div>
    <div class="row mt-3">
      <div class="col-md-12">
        <h2 class="center-text mb-4">Production</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <Spinner v-if="isLoading" />
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Meals</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.meal_orders"
                ref="mealOrdersDates"
              ></delivery-date-picker>
              <b-btn @click="clearMealOrders()" class="ml-1">Clear</b-btn>
            </div>
            <p class="mt-4 center-text">
              Shows how many of each meal to make based on your orders.
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('meal_orders', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item @click="exportData('meal_orders', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('meal_orders', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('meal_orders', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Ingredients</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.ingredient_quantities"
                :rtl="true"
                ref="ingredientQuantitiesDates"
              ></delivery-date-picker>
              <b-btn @click="clearIngredientQuantities()" class="ml-1"
                >Clear</b-btn
              >
            </div>
            <p class="mt-4 center-text">
              Shows how much of each ingredient is needed based on your orders.
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('ingredient_quantities', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item
                    @click="exportData('ingredient_quantities', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData('ingredient_quantities', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData('ingredient_quantities', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <h2 class="center-text mb-4">Bagging & Delivery</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Orders Summary</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.orders_by_customer"
                ref="ordersByCustomerDates"
              ></delivery-date-picker>
              <b-btn @click="clearOrdersByCustomer()" class="ml-1">Clear</b-btn>
            </div>
            <p class="mt-4 center-text">
              Shows how to bag up your meals for each customer.
            </p>

            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('orders_by_customer', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item
                    @click="exportData('orders_by_customer', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData('orders_by_customer', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item
                    @click="exportData('orders_by_customer', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Packing Slips</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.packing_slips"
                :rtl="true"
                ref="packingSlipsDates"
              ></delivery-date-picker>
              <b-btn @click="clearPackingSlips()" class="ml-1">Clear</b-btn>
            </div>
            <p class="mt-4 center-text">
              Show packing slips or order summaries to include in your bag to
              the customers.
            </p>
            <div class="row">
              <div class="col-md-12">
                <button
                  @click="print('packing_slips', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 center"
                >
                  Print
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Delivery Routes</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.delivery_routes"
                ref="deliveryRoutesDates"
              ></delivery-date-picker>
              <b-btn @click="clearDeliveryRoutes()" class="ml-1">Clear</b-btn>
            </div>
            <p class="mt-4 center-text">
              Shows you the quickest route to make your deliveries.
            </p>
            <div class="row">
              <div class="col-md-12">
                <button
                  @click="print('delivery_routes', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 center"
                >
                  Print
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <h2 class="center-text mb-4">Payments</h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body m-sm-4">
            <h4 class="center-text mb-4">Payments</h4>
            <div class="report-date-picker">
              <delivery-date-picker
                v-model="delivery_dates.payments"
                ref="payments"
                :orderDate="true"
              ></delivery-date-picker>
              <b-btn @click="clearDeliveryRoutes()" class="ml-1">Clear</b-btn>
            </div>
            <p class="mt-4 center-text">
              Gives you breakdowns of all your payments as well as totals for
              the date range you pick.
            </p>
            <div class="row">
              <div class="col-md-6">
                <button
                  @click="print('payments', 'pdf')"
                  class="btn btn-primary btn-md center mt-2 pull-right"
                >
                  Print
                </button>
              </div>
              <div class="col-md-6">
                <b-dropdown
                  variant="warning"
                  class="center mt-2"
                  right
                  text="Export as"
                >
                  <b-dropdown-item @click="exportData('payments', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('payments', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('payments', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.report-date-picker {
  display: flex;
  justify-content: center;
}
</style>

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
        packing_slips: [],
        delivery_routes: [],
        payments: []
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
        // const warning = this.checkDateRange({ ...dates });
        // if (report != "payments") {
        //   if (warning) {
        //     try {
        //       let dialog = await this.$dialog.confirm(
        //         "You have selected a date range which includes delivery days which haven't passe" +
        //           "d their cutoff period. This means new orders can still come in for those days. Continue?"
        //       );
        //       dialog.close();
        //     } catch (e) {
        //       return;
        //     }
        //   }
        // }
      }
      if (dates.start && !dates.end) {
        params.delivery_dates = {
          from: dates.start,
          to: dates.start
        };

        // const warning = this.checkDateRange({ ...dates });
        // if (report != "payments") {
        //   if (warning) {
        //     try {
        //       let dialog = await this.$dialog.confirm(
        //         "You have selected a date range which includes delivery days which haven't passe" +
        //           "d their cutoff period. This means new orders can still come in for those days. Continue?"
        //       );
        //       dialog.close();
        //     } catch (e) {
        //       return;
        //     }
        //   }
        // }
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
            "Please confirm that orders exist for the selected date range.",
            "Failed to print report."
          );
        })
        .finally(() => {
          this.loading = false;
        });
    },
    async exportData(report, format = "pdf", print = false) {
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
    },
    clearMealOrders() {
      this.delivery_dates.meal_orders.start = null;
      this.delivery_dates.meal_orders.end = null;
      this.$refs.mealOrdersDates.clearDates();
    },
    clearIngredientQuantities() {
      this.delivery_dates.ingredient_quantities.start = null;
      this.delivery_dates.ingredient_quantities.end = null;
      this.$refs.ingredientQuantitiesDates.clearDates();
    },
    clearOrdersByCustomer() {
      this.delivery_dates.orders_by_customer.start = null;
      this.delivery_dates.orders_by_customer.end = null;
      this.$refs.ordersByCustomerDates.clearDates();
    },
    clearPackingSlips() {
      this.delivery_dates.packing_slips.start = null;
      this.delivery_dates.packing_slips.end = null;
      this.$refs.packingSlipsDates.clearDates();
    },
    clearDeliveryRoutes() {
      this.delivery_dates.delivery_routes.start = null;
      this.delivery_dates.delivery_routes.end = null;
      this.$refs.deliveryRoutesDates.clearDates();
    },
    clearPayments() {
      this.delivery_dates.delivery_routes.start = null;
      this.delivery_dates.delivery_routes.end = null;
      this.$refs.payments.clearDates();
    }
  }
};
</script>
