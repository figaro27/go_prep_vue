<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading" />
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
            v-show="initialized"
          >
            <div slot="beforeTable" class="mb-2">
              <div class="table-before d-flex flex-wrap align-items-center">
                <div class="d-inline-block mr-2 flex-grow-0">
                  <a :href="stripeUrl" target="_blank">
                    <b-btn class="btn btn-success filter-btn"
                      >Stripe Account</b-btn
                    >
                  </a>
                </div>
                <delivery-date-picker
                  v-model="filters.delivery_dates"
                  @change="onChangeDateFilter"
                  class="mt-3 mt-sm-0"
                  ref="deliveryDates"
                  :orderDate="true"
                ></delivery-date-picker>
                <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>
                <p class="pt-3 ml-3">
                  <img
                    v-b-popover.hover="
                      'GoPrep takes ' +
                        goPrepFee * 100 +
                        '% off the Subtotal of the order. The subtotal is the total amount of the meals minus any Meal Plan Discount or Coupon Reduction. This does not include Delivery Fees, Processing Fees, or Sales Tax (on purpose so you can recoup some of the amount paid to GoPrep). Stripe takes 2.9% of the Total amount plus .30 cents per transaction.'
                    "
                    src="/images/store/popover.png"
                    class="popover-size mr-2"
                  />Fees
                </p>
                <b-form-select
                  v-model="filters.couponCode"
                  :options="coupons"
                  class="ml-3"
                  v-if="coupons.length > 0"
                >
                  <template slot="first">
                    <option :value="null">All Coupons</option>
                  </template>
                </b-form-select>
              </div>
            </div>

            <span slot="beforeLimit">
              <b-btn
                variant="primary"
                @click="exportData('payments', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print Report
              </b-btn>
              <b-dropdown class="mx-1 mt-2 mt-sm-0" right text="Export as">
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
            </span>
            <div slot="created_at" slot-scope="props">
              <span v-if="props.row.created_at != 'TOTALS'">{{
                moment(props.row.created_at).format("dddd, MMM Do")
              }}</span>
              <span v-if="props.row.created_at === 'TOTALS'">{{
                props.row.created_at
              }}</span>
            </div>
            <div slot="subtotal" slot-scope="props">
              <div>
                {{
                  formatMoney(props.row.preFeePreDiscount, props.row.currency)
                }}
              </div>
            </div>
            <div slot="mealPlanDiscount" slot-scope="props">
              <div>
                {{
                  formatMoney(props.row.mealPlanDiscount, props.row.currency)
                }}
              </div>
            </div>
            <div slot="couponCode" slot-scope="props">
              <div>{{ props.row.couponCode }}</div>
            </div>
            <div slot="couponReduction" slot-scope="props">
              <div>
                {{ formatMoney(props.row.couponReduction, props.row.currency) }}
              </div>
            </div>
            <div slot="processingFee" slot-scope="props">
              <div>
                {{ formatMoney(props.row.processingFee, props.row.currency) }}
              </div>
            </div>
            <div slot="deliveryFee" slot-scope="props">
              <div>
                {{ formatMoney(props.row.deliveryFee, props.row.currency) }}
              </div>
            </div>
            <div slot="salesTax" slot-scope="props">
              <div>
                {{ formatMoney(props.row.salesTax, props.row.currency) }}
              </div>
            </div>
            <div slot="total" slot-scope="props">
              <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
            </div>
            <div slot="goPrepFee" slot-scope="props">
              <div>
                {{
                  formatMoney(
                    props.row.afterDiscountBeforeFees * goPrepFee,
                    props.row.currency
                  )
                }}
              </div>
            </div>
            <div slot="stripeFee" slot-scope="props">
              <div v-if="props.row.amount > 0">
                {{
                  formatMoney(
                    props.row.amount * stripeFee + 0.3,
                    props.row.currency
                  )
                }}
              </div>
              <div v-else>
                $0.00
              </div>
            </div>
            <div slot="grandTotal" slot-scope="props">
              <div v-if="props.row.amount > 0">
                {{
                  formatMoney(
                    props.row.amount -
                      (props.row.afterDiscountBeforeFees * goPrepFee +
                        props.row.amount * stripeFee) -
                      0.3,
                    props.row.currency
                  )
                }}
              </div>
              <div v-else>
                $0.00
              </div>
            </div>
            <div slot="deposit" slot-scope="props">
              <div>
                {{ formatMoney(100 - props.row.deposit, props.row.currency) }}
              </div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import format from "../../lib/format";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      goPrepFee: 0,
      stripeFee: 0.029,
      stripeUrl: "",
      ordersByDate: {},
      filter: false,
      pastOrder: false,
      filters: {
        delivery_dates: {
          start: null,
          end: null
        },
        couponCode: null
      },
      order: {},
      orderId: "",
      user_detail: {},
      options: {
        headings: {
          notes: "Notes",
          order_number: "Order #",
          "user.user_detail.full_name": "Name",
          created_at: "Payment Date",
          subtotal: "Subtotal",
          mealPlanDiscount: "Meal Plan Discount",
          couponCode: "Coupon",
          couponReduction: "Coupon Reduction",
          processingFee: "Processing Fee",
          deliveryFee: "Delivery Fee",
          salesTax: "Sales Tax",
          total: "PreFee Total",
          goPrepFee: "GoPrep Fee",
          stripeFee: "Stripe Fee",
          grandTotal: "Total",
          deposit: "Balance Remaining"
        },
        customSorting: {
          created_at: function(ascending) {
            return function(a, b) {
              a = a.created_at;
              b = b.created_at;

              if (ascending) return a.isBefore(b, "day") ? 1 : -1;
              return a.isAfter(b, "day") ? 1 : -1;
            };
          }
        },
        rowClassCallback: function(row) {
          let classes = `payment-${row.id}`;
          classes += row.sumRow ? " strong" : "";
          return classes;
        }
      }
    };
  },
  created() {
    this.refreshViewedStore();
    axios.get("/api/me/stripe/login").then(resp => {
      if (resp.data.url) {
        this.stripeUrl = resp.data.url;
      }
    });
  },
  mounted() {
    this.getApplicationFee();
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCoupons: "storeCoupons",
      ordersToday: "storeOrdersToday",
      upcomingOrders: "storeUpcomingOrders",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      nextDeliveryDates: "storeNextDeliveryDates",
      getMeal: "storeMeal"
    }),
    tableData() {
      let filters = { ...this.filters };

      let orders = {};
      if (this.filters.delivery_dates.start === null) {
        orders = this.ordersToday;
      } else {
        orders = this.ordersByDate;
      }

      if (this.filters.couponCode != null) {
        orders = orders.filter(
          order => order.couponCode === this.filters.couponCode
        );
      }

      let totalsRowCheck = 0;
      orders.forEach(order => {
        if (order.created_at === "TOTALS") {
          totalsRowCheck = 1;
        }
      });

      if (!totalsRowCheck) {
        let sums = {
          preFeePreDiscount: 0,
          mealPlanDiscount: 0,
          couponReduction: 0,
          afterDiscountBeforeFees: 0,
          processingFee: 0,
          deliveryFee: 0,
          salesTax: 0,
          amount: 0
        };

        orders.forEach(order => {
          sums.preFeePreDiscount += order.preFeePreDiscount;
          sums.mealPlanDiscount += order.mealPlanDiscount;
          sums.couponReduction += order.couponReduction;
          sums.afterDiscountBeforeFees += order.afterDiscountBeforeFees;
          sums.processingFee += order.processingFee;
          sums.deliveryFee += order.deliveryFee;
          sums.salesTax += order.salesTax;
          sums.amount += order.amount;
        });

        orders.unshift({
          created_at: "TOTALS",
          preFeePreDiscount: sums.preFeePreDiscount,
          mealPlanDiscount: sums.mealPlanDiscount,
          couponReduction: sums.couponReduction,
          afterDiscountBeforeFees: sums.afterDiscountBeforeFees,
          processingFee: sums.processingFee,
          deliveryFee: sums.deliveryFee,
          salesTax: sums.salesTax,
          amount: sums.amount,
          stripeFee: sums.stripeFee,
          sumRow: 1
        });
      }
      return orders;
    },
    columns() {
      let columns = [
        "created_at",
        "subtotal",
        "salesTax",
        "total",
        "goPrepFee",
        "stripeFee",
        "grandTotal"
      ];

      let addedColumns = [];

      this.upcomingOrders.forEach(order => {
        if (!columns.includes("couponCode") && order.couponCode != null) {
          columns.splice(2, 0, "couponReduction");
          columns.splice(2, 0, "couponCode");
        }
        if (
          !columns.includes("mealPlanDiscount") &&
          order.mealPlanDiscount > 0
        ) {
          columns.splice(2, 0, "mealPlanDiscount");
        }
        if (!columns.includes("processingFee") && order.processingFee > 0) {
          columns.splice(2, 0, "processingFee");
        }
        if (!columns.includes("deliveryFee") && order.deliveryFee > 0) {
          columns.splice(2, 0, "deliveryFee");
        }
        if (!columns.includes("deposit") && order.deposit < 100) {
          columns.splice(columns.length, 0, "deposit");
        }
      });

      return columns;
    },
    coupons() {
      let coupons = [];
      if (this.storeCoupons.length > 0) {
        this.storeCoupons.forEach(coupon => {
          coupons.push(coupon.code);
        });
      }
      return coupons;
    }
  },
  methods: {
    ...mapActions({
      refreshOrders: "refreshOrders",
      refreshUpcomingOrders: "refreshUpcomingOrders",
      refreshOrdersToday: "refreshOrdersToday",
      updateOrder: "updateOrder",
      refreshViewedStore: "refreshViewedStore"
    }),
    refreshTable() {
      this.refreshOrders();
    },
    formatMoney: format.money,
    syncEditables() {
      this.editing = _.keyBy({ ...this.tableData }, "id");
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, ["id", id]);
    },
    getTableDataById(id) {
      return _.find(this.tableData, ["id", id]);
    },
    async exportData(report, format = "pdf", print = false) {
      const warning = this.checkDateRange({ ...this.filters.delivery_dates });

      let params = {};

      if (
        this.filters.delivery_dates.start &&
        this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.end
        };
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
    onChangeDateFilter() {
      axios
        .post("/api/me/getOrdersWithDates", {
          start: this.filters.delivery_dates.start,
          end: this.filters.delivery_dates.end,
          payments: 1
        })
        .then(response => {
          this.ordersByDate = response.data;
        });
    },
    updateViewedOrders() {
      axios.get(`/api/me/ordersUpdateViewed`);
    },
    clearDeliveryDates() {
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.$refs.deliveryDates.clearDates();
    },
    getApplicationFee() {
      axios.get("/api/me/getApplicationFee").then(resp => {
        this.goPrepFee = resp.data / 100;
      });
    }
  }
};
</script>
