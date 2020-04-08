<template>
  <div class="row mt-3">
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
                    <b-btn class="btn btn-success">Stripe Account</b-btn>
                  </a>
                </div>

                <delivery-date-picker
                  v-model="filters.delivery_dates"
                  @change="onChangeDateFilter"
                  class="mt-3 mt-sm-0"
                  ref="deliveryDates"
                  :orderDate="filters.byOrderDate"
                ></delivery-date-picker>
                <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>
                <b-form-checkbox
                  class="mediumCheckbox ml-3"
                  type="checkbox"
                  v-model="filters.byOrderDate"
                  :value="1"
                  :unchecked-value="0"
                  @input="toggleByOrderDate"
                  ><span class="paragraph">By Order Date</span></b-form-checkbox
                >
                <!-- <p class="pt-3 ml-3">
                  <img
                    v-b-popover.hover="
                      'GoPrep takes ' +
                        goPrepFee * 100 +
                        '% off the Subtotal of the order. The subtotal is the total amount of the items minus any Subscription Discount or Coupon Reduction. This does not include Delivery Fees, Processing Fees, or Sales Tax (on purpose so you can recoup some of the amount paid to GoPrep). Stripe takes 2.9% of the Total amount plus .30 cents per transaction.'
                    "
                    src="/images/store/popover.png"
                    class="popover-size mr-2"
                  />Fees
                </p> -->

                <!-- Add back in and make it work with delivery / order dates -->

                <!-- <b-form-checkbox
                  class="mediumCheckbox ml-3"
                  type="checkbox"
                  v-model="filters.dailySummary"
                  :value="1"
                  :unchecked-value="0"
                  ><span class="paragraph">Daily Summary</span></b-form-checkbox
                > -->

                <b-form-select
                  v-model="filters.couponCode"
                  :options="coupons"
                  class="ml-3 w-180"
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
            <div slot="delivery_date" slot-scope="props">
              <span v-if="props.row.created_at != 'TOTALS'">{{
                moment(props.row.delivery_date).format("dddd, MMM Do")
              }}</span>
              <span v-if="props.row.created_at === 'TOTALS'">{{
                props.row.delivery_date
              }}</span>
            </div>
            <div slot="subtotal" slot-scope="props">
              <div>
                {{
                  formatMoney(props.row.preFeePreDiscount, props.row.currency)
                }}
              </div>
            </div>
            <!-- <div slot="couponCode" slot-scope="props">
              <div>{{ props.row.couponCode }}</div>
            </div> -->
            <div slot="couponReduction" slot-scope="props">
              <div class="text-success">
                {{
                  props.row.couponReduction !== null &&
                  props.row.couponReduction > 0
                    ? "(" +
                      formatMoney(
                        props.row.couponReduction,
                        props.row.currency
                      ) +
                      ")"
                    : " - "
                }}
              </div>
            </div>
            <div slot="mealPlanDiscount" slot-scope="props">
              <div class="text-success">
                {{
                  props.row.mealPlanDiscount !== null &&
                  props.row.mealPlanDiscount > 0
                    ? "(" +
                      formatMoney(
                        props.row.mealPlanDiscount,
                        props.row.currency
                      ) +
                      ")"
                    : " - "
                }}
              </div>
            </div>
            <div slot="salesTax" slot-scope="props">
              <div>
                {{
                  props.row.salesTax !== null && props.row.salesTax > 0
                    ? formatMoney(props.row.salesTax, props.row.currency)
                    : " - "
                }}
              </div>
            </div>
            <div slot="processingFee" slot-scope="props">
              <div>
                {{
                  props.row.processingFee !== null &&
                  props.row.processingFee > 0
                    ? formatMoney(props.row.processingFee, props.row.currency)
                    : " - "
                }}
              </div>
            </div>
            <div slot="deliveryFee" slot-scope="props">
              <div>
                {{
                  props.row.deliveryFee !== null && props.row.deliveryFee > 0
                    ? formatMoney(props.row.deliveryFee, props.row.currency)
                    : " - "
                }}
              </div>
            </div>

            <!-- <div slot="total" slot-scope="props">
              <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
            </div> -->
            <div slot="goprep_fee" slot-scope="props">
              <div>
                {{ formatMoney(props.row.goprep_fee, props.row.currency) }}
              </div>
            </div>
            <div slot="stripe_fee" slot-scope="props">
              <div>
                {{ formatMoney(props.row.stripe_fee, props.row.currency) }}
              </div>
            </div>
            <div slot="referralReduction" slot-scope="props">
              <div class="text-success">
                {{
                  props.row.referralReduction !== null &&
                  props.row.referralReduction > 0
                    ? "(" +
                      formatMoney(
                        props.row.referralReduction,
                        props.row.currency
                      ) +
                      ")"
                    : " - "
                }}
              </div>
            </div>
            <div slot="purchasedGiftCardReduction" slot-scope="props">
              <div class="text-success">
                {{
                  props.row.purchasedGiftCardReduction !== null &&
                  props.row.purchasedGiftCardReduction > 0
                    ? "(" +
                      formatMoney(
                        props.row.purchasedGiftCardReduction,
                        props.row.currency
                      ) +
                      ")"
                    : " - "
                }}
              </div>
            </div>
            <div slot="promotionReduction" slot-scope="props">
              <div class="text-success">
                {{
                  props.row.promotionReduction !== null &&
                  props.row.promotionReduction > 0
                    ? "(" +
                      formatMoney(
                        props.row.promotionReduction,
                        props.row.currency
                      ) +
                      ")"
                    : " - "
                }}
              </div>
            </div>
            <div slot="pointsReduction" slot-scope="props">
              <div class="text-success">
                {{
                  props.row.pointsReduction !== null &&
                  props.row.pointsReduction > 0
                    ? "(" +
                      formatMoney(
                        props.row.pointsReduction,
                        props.row.currency
                      ) +
                      ")"
                    : " - "
                }}
              </div>
            </div>
            <div slot="amount" slot-scope="props">
              <div>
                {{ formatMoney(props.row.amount, props.row.currency) }}
              </div>
            </div>
            <div slot="balance" slot-scope="props">
              <div>
                <!-- {{ formatMoney((100 - props.row.deposit)/100 * props.row.grandTotal, props.row.currency) }} -->
                {{
                  props.row.balance !== null && props.row.balance > 0
                    ? formatMoney(props.row.balance, props.row.currency)
                    : " - "
                }}
              </div>
            </div>
            <div slot="refundedAmount" slot-scope="props">
              <div>
                {{ formatMoney(props.row.refundedAmount, props.row.currency) }}
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
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      upcomingOrdersByOrderDate: [],
      goPrepFee: 0.05,
      stripeFee: 0.029,
      stripeUrl: "",
      ordersByDate: [],
      filter: false,
      pastOrder: false,
      filters: {
        delivery_dates: {
          start: null,
          end: null
        },
        couponCode: null,
        dailySummary: 0,
        byOrderDate: 0
      },
      order: {},
      orderId: "",
      user_detail: {},
      options: {
        headings: {
          created_at: "Order Date",
          delivery_date: "Delivery Date",
          totalOrders: "Orders",
          subtotal: "Subtotal",
          couponReduction: "Coupon",
          mealPlanDiscount: "Subscription",
          salesTax: "Sales Tax",
          processingFee: "Processing Fee",
          deliveryFee: "Delivery Fee",
          // total: "PreFee Total",
          goprep_fee: "GoPrep Fee",
          stripe_fee: "Stripe Fee",
          referralReduction: "Referral",
          purchasedGiftCardReduction: "Gift Card",
          promotionReduction: "Promotion",
          pointsReduction: "Points",
          amount: "Total",
          balance: "Balance",
          refundedAmount: "Refunded"
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
      upcomingOrdersWithoutItems: "storeUpcomingOrdersWithoutItems",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      nextDeliveryDates: "storeNextDeliveryDates",
      getMeal: "storeMeal"
    }),
    tableData() {
      let filters = { ...this.filters };

      let orders = [];
      if (this.filters.delivery_dates.start === null) {
        if (!this.filters.byOrderDate) {
          orders = [...this.upcomingOrdersWithoutItems];
        } else {
          orders = this.upcomingOrdersByOrderDate;
        }
      } else {
        orders = this.ordersByDate;
      }

      orders = orders.filter(order => order.voided === 0);

      // if (this.filters.byDeliveryDate) {
      //   orders = this.upcomingOrders;
      // }

      if (this.filters.couponCode != null) {
        orders = orders.filter(
          order => order.couponCode === this.filters.couponCode
        );
      }

      let grandTotalOrders = orders.length - 1;

      if (this.filters.dailySummary) {
        let ordersByDay = Object.values(_.groupBy(orders, "order_day"));

        orders = [];

        ordersByDay.forEach(orderByDay => {
          let created_at = "";
          let totalOrders = 0;
          let sums = {
            preFeePreDiscount: 0,
            couponReduction: 0,
            mealPlanDiscount: 0,
            afterDiscountBeforeFees: 0,
            salesTax: 0,
            processingFee: 0,
            deliveryFee: 0,
            referralReduction: 0,
            purchasedGiftCardReduction: 0,
            promotionReduction: 0,
            pointsReduction: 0,
            // goprep_fee: 0,
            // stripe_fee: 0,
            amount: 0,
            balance: 0
          };

          orderByDay.forEach(order => {
            created_at = order.paid_at;
            totalOrders += 1;
            sums.preFeePreDiscount += order.preFeePreDiscount;
            sums.couponReduction += order.couponReduction;
            sums.mealPlanDiscount += order.mealPlanDiscount;
            sums.afterDiscountBeforeFees += order.afterDiscountBeforeFees;
            sums.salesTax += order.salesTax;
            sums.processingFee += order.processingFee;
            sums.deliveryFee += order.deliveryFee;
            sums.referralReduction += order.referralReduction;
            sums.purchasedGiftCardReduction += order.purchasedGiftCardReduction;
            sums.promotionReduction += order.promotionReduction;
            sums.pointsReduction += order.pointsReduction;
            sums.amount += order.amount;
            sums.balance += order.balance;
          });
          orders.push({
            created_at: created_at,
            totalOrders: totalOrders,
            preFeePreDiscount: sums.preFeePreDiscount,
            couponReduction: sums.couponReduction,
            mealPlanDiscount: sums.mealPlanDiscount,
            afterDiscountBeforeFees: sums.afterDiscountBeforeFees,
            salesTax: sums.salesTax,
            processingFee: sums.processingFee,
            deliveryFee: sums.deliveryFee,
            referralReduction: sums.referralReduction,
            purchasedGiftCardReduction: sums.purchasedGiftCardReduction,
            promotionReduction: sums.promotionReduction,
            pointsReduction: sums.pointsReduction,
            amount: sums.amount,
            balance: sums.balance
          });
        });

        orders.shift();
      }

      let totalsRowCheck = 0;
      orders.forEach(order => {
        if (order.created_at === "TOTALS") {
          totalsRowCheck = 1;
        }
      });

      if (!totalsRowCheck) {
        let totalOrders = 0;
        let sums = {
          preFeePreDiscount: 0,
          couponReduction: 0,
          mealPlanDiscount: 0,
          afterDiscountBeforeFees: 0,
          salesTax: 0,
          processingFee: 0,
          deliveryFee: 0,
          goprep_fee: 0,
          stripe_fee: 0,
          referralReduction: 0,
          purchasedGiftCardReduction: 0,
          promotionReduction: 0,
          pointsReduction: 0,
          amount: 0,
          balance: 0,
          refundedAmount: 0
        };

        orders.forEach(order => {
          sums.preFeePreDiscount += order.preFeePreDiscount;
          sums.couponReduction += order.couponReduction;
          sums.mealPlanDiscount += order.mealPlanDiscount;
          sums.afterDiscountBeforeFees += order.afterDiscountBeforeFees;
          sums.salesTax += order.salesTax;
          sums.processingFee += order.processingFee;
          sums.deliveryFee += order.deliveryFee;
          sums.referralReduction += order.referralReduction;
          sums.purchasedGiftCardReduction += order.purchasedGiftCardReduction;
          sums.promotionReduction += order.promotionReduction;
          sums.pointsReduction += order.pointsReduction;
          sums.amount += order.amount;
          sums.balance += order.balance;
          sums.refundedAmount += order.refundedAmount;
        });

        orders.unshift({
          created_at: "TOTALS",
          totalOrders: grandTotalOrders,
          preFeePreDiscount: sums.preFeePreDiscount,
          couponReduction: sums.couponReduction,
          mealPlanDiscount: sums.mealPlanDiscount,
          afterDiscountBeforeFees: sums.afterDiscountBeforeFees,
          salesTax: sums.salesTax,
          processingFee: sums.processingFee,
          deliveryFee: sums.deliveryFee,
          referralReduction: sums.referralReduction,
          purchasedGiftCardReduction: sums.purchasedGiftCardReduction,
          promotionReduction: sums.promotionReduction,
          pointsReduction: sums.pointsReduction,
          amount: sums.amount,
          balance: sums.balance,
          refundedAmount: sums.refundedAmount,
          sumRow: 1
        });
      }

      return orders;
    },
    columns() {
      let columns = [
        "created_at",
        "delivery_date",
        "subtotal",
        "salesTax",
        // "total",

        "amount",
        "balance"
      ];

      let addedColumns = [];

      this.upcomingOrdersWithoutItems.forEach(order => {
        if (!columns.includes("couponCode") && order.couponCode != null) {
          columns.splice(3, 0, "couponReduction");
          // columns.splice(3, 0, "couponCode");
        }

        if (!columns.includes("salesTax") && order.salesTax > 0) {
          columns.splice(3, 0, "salesTax");
        }

        if (!columns.includes("processingFee") && order.processingFee > 0) {
          columns.splice(4, 0, "processingFee");
        }
        if (!columns.includes("deliveryFee") && order.deliveryFee > 0) {
          columns.splice(4, 0, "deliveryFee");
        }

        if (!columns.includes("refundedAmount") && order.refundedAmount > 0) {
          columns.splice(columns.length, 0, "refundedAmount");
        }
      });

      this.ordersByDate.forEach(order => {
        if (!columns.includes("couponReduction") && order.couponCode != null) {
          columns.splice(3, 0, "couponReduction");
          // columns.splice(3, 0, "couponCode");
        }

        if (!columns.includes("processingFee") && order.processingFee > 0) {
          columns.splice(4, 0, "processingFee");
        }
        if (!columns.includes("deliveryFee") && order.deliveryFee > 0) {
          columns.splice(4, 0, "deliveryFee");
        }

        if (
          !columns.includes("purchasedGiftCardReduction") &&
          order.purchasedGiftCardReduction > 0
        ) {
          columns.splice(columns.length - 2, 0, "purchasedGiftCardReduction");
        }

        if (
          !columns.includes("referralReduction") &&
          order.referralReduction > 0
        ) {
          columns.splice(columns.length - 2, 0, "referralReduction");
        }

        if (
          !columns.includes("promotionReduction") &&
          order.promotionReduction > 0
        ) {
          columns.splice(columns.length - 2, 0, "promotionReduction");
        }

        if (!columns.includes("pointsReduction") && order.pointsReduction > 0) {
          columns.splice(columns.length - 2, 0, "pointsReduction");
        }

        if (!columns.includes("refundedAmount") && order.refundedAmount > 0) {
          columns.splice(columns.length - 1, 0, "refundedAmount");
        }
      });

      if (this.filters.dailySummary) {
        columns.splice(1, 0, "totalOrders");
      }

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
      refreshUpcomingOrdersWithoutItems: "refreshUpcomingOrdersWithoutItems",
      refreshOrdersToday: "refreshOrdersToday",
      updateOrder: "updateOrder"
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

      params.couponCode = this.filters.couponCode;
      params.dailySummary = this.filters.dailySummary;
      params.byOrderDate = this.filters.byOrderDate;

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
        .post("/api/me/getOrdersWithDatesWithoutItems", {
          start: this.filters.delivery_dates.start,
          end: this.filters.delivery_dates.end,
          payments: this.filters.byOrderDate
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
      this.toggleByOrderDate();
    },
    getApplicationFee() {
      axios.get("/api/me/getApplicationFee").then(resp => {
        this.goPrepFee = resp.data / 100;
      });
    },
    toggleByOrderDate() {
      if (this.filters.delivery_dates.start !== null) {
        this.onChangeDateFilter();
      } else {
        axios
          .post("/api/me/getOrdersToday", {
            payments: this.filters.byOrderDate
          })
          .then(response => {
            this.upcomingOrdersByOrderDate = response.data;
          });
      }
    }
  }
};
</script>
