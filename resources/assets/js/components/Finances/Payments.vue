<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <b-modal
        size="sm"
        title="Enter Password"
        v-model="showMultiAuthModal"
        v-if="showMultiAuthModal"
        no-fade
        no-close-on-backdrop
        hide-header
        hide-footer
      >
        <b-form
          @submit.prevent="submitMultiAuthPassword"
          class="pt-3 pl-3 pr-3"
        >
          <p class="center-text strong">Enter Password</p>
          <b-form-group horizontal>
            <b-input
              v-model="multiAuthPassword"
              type="password"
              required
            ></b-input>
          </b-form-group>
          <b-form-group horizontal class="center-text">
            <button type="submit" class="btn btn-primary">Submit</button>
          </b-form-group>
        </b-form>
        <b-form-group horizontal class="center-text">
          <button class="btn btn-warning" @click="cancel">Go Back</button>
        </b-form-group>
      </b-modal>
      <b-modal
        size="md"
        title="Filters"
        v-model="showFiltersModal"
        v-if="showFiltersModal"
        no-fade
        hide-header
      >
        <div style="margin-left:155px">
          <div class="mb-4 mt-4" style="position:relative;right:18px">
            <b-form-radio-group
              v-model="filters.byPaymentDate"
              :options="[
                { text: 'Order Dates', value: true },
                { text: 'Delivery Dates', value: false }
              ]"
              required
            ></b-form-radio-group>
          </div>
          <div class="mb-4">
            <b-form-checkbox
              class="mediumCheckbox"
              type="checkbox"
              v-model="filters.dailySummary"
              ><span class="paragraph">Daily Summary</span></b-form-checkbox
            >
          </div>
          <div class="mb-4">
            <b-form-checkbox
              class="mediumCheckbox"
              type="checkbox"
              v-model="filters.removeManualOrders"
              ><span class="paragraph"
                >Ignore Manual Orders</span
              ></b-form-checkbox
            >
          </div>
          <div class="mb-4">
            <b-form-checkbox
              class="mediumCheckbox"
              type="checkbox"
              v-model="filters.removeCashOrders"
              ><span class="paragraph"
                >Ignore Cash Orders</span
              ></b-form-checkbox
            >
          </div>
          <div class="mb-4">
            <b-form-select
              v-model="filters.couponId"
              :options="coupons"
              class="w-180"
              style="position:relative;right:20px"
              v-if="coupons.length > 0"
            >
              <template slot="first">
                <option :value="null">All Coupons</option>
              </template>
            </b-form-select>
          </div>
        </div>
      </b-modal>
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
              class="mt-3 mt-sm-0"
              ref="deliveryDates"
              :orderDate="filters.byPaymentDate"
            ></delivery-date-picker>
            <b-btn @click="clearDeliveryDates()" class="ml-2">Clear</b-btn>

            <b-btn
              variant="primary"
              @click="showFiltersModal = true"
              class="ml-2"
              >Filters</b-btn
            >
          </div>
        </div>

        <span slot="beforeLimit">
          <div class="d-flex">
            <b-form-checkbox
              v-if="store.id === 3 || store.id === 196"
              v-model="upcharges"
              :value="true"
              :unchecked-value="false"
              class="d-inline mr-2 pt-2"
            >
              Upcharge Report
            </b-form-checkbox>
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
          </div>
        </span>
        <div slot="created_at" slot-scope="props">
          <span
            v-if="props.row.created_at && props.row.created_at != 'TOTALS'"
            >{{ moment(props.row.created_at).format("dddd, MMM Do") }}</span
          >
          <span
            v-if="props.row.created_at && props.row.created_at === 'TOTALS'"
            >{{ props.row.created_at }}</span
          >
        </div>
        <div slot="delivery_date" slot-scope="props">
          <span
            v-if="
              props.row.delivery_date && props.row.delivery_date !== 'TOTALS'
            "
            >{{ moment(props.row.delivery_date).format("dddd, MMM Do") }}</span
          >
          <span
            v-if="
              props.row.delivery_date && props.row.delivery_date === 'TOTALS'
            "
            >{{ props.row.delivery_date }}</span
          >
        </div>
        <div slot="subtotal" slot-scope="props">
          <div>
            {{ formatMoney(props.row.preFeePreDiscount, props.row.currency) }}
          </div>
        </div>
        <!-- <div slot="couponCode" slot-scope="props">
              <div>{{ props.row.couponCode }}</div>
            </div> -->
        <div slot="couponReduction" slot-scope="props">
          <div>
            {{
              props.row.couponReduction !== null &&
              props.row.couponReduction > 0
                ? "(" +
                  formatMoney(props.row.couponReduction, props.row.currency) +
                  ")"
                : " - "
            }}
          </div>
        </div>
        <div slot="mealPlanDiscount" slot-scope="props">
          <div>
            {{
              props.row.mealPlanDiscount !== null &&
              props.row.mealPlanDiscount > 0
                ? "(" +
                  formatMoney(props.row.mealPlanDiscount, props.row.currency) +
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
              props.row.processingFee !== null && props.row.processingFee > 0
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
        <div slot="purchasedGiftCardReduction" slot-scope="props">
          <div>
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
        <div slot="gratuity" slot-scope="props">
          <div>
            {{ formatMoney(props.row.gratuity, props.row.currency) }}
          </div>
        </div>

        <div slot="coolerDeposit" slot-scope="props">
          <div>
            {{ formatMoney(props.row.coolerDeposit, props.row.currency) }}
          </div>
        </div>

        <div slot="referralReduction" slot-scope="props">
          <div>
            {{
              props.row.referralReduction !== null &&
              props.row.referralReduction > 0
                ? "(" +
                  formatMoney(props.row.referralReduction, props.row.currency) +
                  ")"
                : " - "
            }}
          </div>
        </div>
        <div slot="promotionReduction" slot-scope="props">
          <div>
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
          <div>
            {{
              props.row.pointsReduction !== null &&
              props.row.pointsReduction > 0
                ? "(" +
                  formatMoney(props.row.pointsReduction, props.row.currency) +
                  ")"
                : " - "
            }}
          </div>
        </div>
        <div slot="chargedAmount" slot-scope="props">
          <div>
            {{
              props.row.chargedAmount !== null && props.row.chargedAmount > 0
                ? formatMoney(props.row.chargedAmount, props.row.currency)
                : " - "
            }}
          </div>
        </div>
        <div slot="preTransactionFeeAmount" slot-scope="props">
          <div>
            {{
              formatMoney(props.row.preTransactionFeeAmount, props.row.currency)
            }}
          </div>
        </div>

        <div slot="transactionFee" slot-scope="props">
          <div>
            {{
              props.row.transactionFee !== null && props.row.transactionFee > 0
                ? "(" +
                  formatMoney(props.row.transactionFee, props.row.currency) +
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
        <div slot="refundedAmount" slot-scope="props">
          <div>
            {{
              props.row.refundedAmount !== null && props.row.refundedAmount > 0
                ? "(" +
                  formatMoney(props.row.refundedAmount, props.row.currency) +
                  ")"
                : " - "
            }}
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
      </v-client-table>
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
      payments: [],
      upcharges: false,
      showFiltersModal: false,
      showMultiAuthModal: false,
      multiAuthPassword: null,
      upcomingOrdersByOrderDate: [],
      stripeUrl: "",
      ordersByDate: [],
      filter: false,
      pastOrder: false,
      filters: {
        startDate: null,
        endDate: null,
        delivery_dates: {
          from: null,
          to: null
        },
        couponId: null,
        dailySummary: false,
        byPaymentDate: true,
        removeManualOrders: false,
        removeCashOrders: false
      },
      order: {},
      orderId: "",
      user_detail: {},
      options: {
        headings: {
          created_at: "Order Date",
          delivery_date: "Delivery Date",
          totalPayments: "# Payments",
          subtotal: "Subtotal",
          couponReduction: "Coupon",
          mealPlanDiscount: "Subscription",
          salesTax: "Sales Tax",
          processingFee: "Processing Fee",
          deliveryFee: "Delivery Fee",
          purchasedGiftCardReduction: "Gift Card",
          gratuity: "Gratuity",
          coolerDeposit: "Cooler Deposit",
          referralReduction: "Referral",
          promotionReduction: "Promotion",
          pointsReduction: "Points",
          chargedAmount: "Additional Charges",
          preTransactionFeeAmount: "Pre-Fee Total",
          transactionFee: "Transaction Fee",
          amount: "Total",
          refundedAmount: "Refunded",
          balance: "Balance"
        },
        rowClassCallback: function(row) {
          let classes = `payment-${row.id}`;
          classes += row.sumRow ? " strong" : "";
          return classes;
        }
      }
    };
  },
  props: {
    tabs: null
  },
  watch: {
    tabs(val) {
      if (val == 0) {
        this.refreshPayments();
      }
    },
    filters: {
      handler() {
        this.refreshPayments();
      },
      deep: true
    }
  },
  created() {
    axios.get("/api/me/stripe/login").then(resp => {
      if (resp.data.url) {
        this.stripeUrl = resp.data.url;
      }
    });
  },
  mounted() {
    if (this.store.modules.multiAuth) {
      this.showMultiAuthModal = true;
    }
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeCoupons: "storeCoupons",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      nextDeliveryDates: "storeNextDeliveryDates"
    }),
    tableData() {
      let filters = { ...this.filters };

      let payments = [];

      payments = this.payments;

      payments = payments.filter(payment => payment.voided === 0);

      let totalPayments = payments.length;

      if (this.filters.dailySummary) {
        let dayType = this.filters.byPaymentDate
          ? "order_day"
          : "delivery_date";
        let paymentsByDay = Object.values(_.groupBy(payments, dayType));

        payments = [];

        paymentsByDay.forEach(paymentByDay => {
          let created_at = "";
          let delivery_date = "";
          let totalPayments = 0;
          let sums = {
            totalPayments: 0,
            preFeePreDiscount: 0,
            couponReduction: 0,
            mealPlanDiscount: 0,
            salesTax: 0,
            processingFee: 0,
            deliveryFee: 0,
            purchasedGiftCardReduction: 0,
            gratuity: 0,
            coolerDeposit: 0,
            referralReduction: 0,
            promotionReduction: 0,
            pointsReduction: 0,
            chargedAmount: 0,
            preTransactionFeeAmount: 0,
            transactionFee: 0,
            amount: 0,
            refundedAmount: 0,
            balance: 0
          };

          paymentByDay.forEach(payment => {
            created_at = payment.paid_at;
            delivery_date = payment.delivery_date;
            totalPayments += 1;
            sums.preFeePreDiscount += payment.preFeePreDiscount;
            sums.couponReduction += payment.couponReduction;
            sums.mealPlanDiscount += payment.mealPlanDiscount;
            sums.salesTax += payment.salesTax;
            sums.processingFee += payment.processingFee;
            sums.deliveryFee += payment.deliveryFee;
            sums.purchasedGiftCardReduction +=
              payment.purchasedGiftCardReduction;
            sums.gratuity += payment.gratuity;
            sums.coolerDeposit += payment.coolerDeposit;
            sums.referralReduction += payment.referralReduction;
            sums.promotionReduction += payment.promotionReduction;
            sums.pointsReduction += payment.pointsReduction;
            sums.chargedAmount += payment.chargedAmount;
            sums.preTransactionFeeAmount += payment.preTransactionFeeAmount;
            sums.transactionFee += payment.transactionFee;
            sums.amount += payment.amount;
            sums.refundedAmount += payment.refundedAmount;
            sums.balance += payment.balance;
          });
          payments.push({
            created_at: dayType == "order_day" ? created_at : null,
            delivery_date: dayType == "delivery_date" ? delivery_date : null,
            totalPayments: totalPayments,
            preFeePreDiscount: sums.preFeePreDiscount,
            couponReduction: sums.couponReduction,
            mealPlanDiscount: sums.mealPlanDiscount,
            salesTax: sums.salesTax,
            processingFee: sums.processingFee,
            deliveryFee: sums.deliveryFee,
            purchasedGiftCardReduction: sums.purchasedGiftCardReduction,
            gratuity: sums.gratuity,
            coolerDeposit: sums.coolerDeposit,
            referralReduction: sums.referralReduction,
            promotionReduction: sums.promotionReduction,
            pointsReduction: sums.pointsReduction,
            chargedAmount: sums.chargedAmount,
            preTransactionFeeAmount: sums.preTransactionFeeAmount,
            transactionFee: sums.transactionFee,
            amount: sums.amount,
            refundedAmount: sums.refundedAmount,
            balance: sums.balance
          });
        });
      }

      let totalsRowCheck = 0;
      payments.forEach(payment => {
        if (payment.created_at === "TOTALS") {
          totalsRowCheck = 1;
        }
      });

      if (!totalsRowCheck) {
        let sums = {
          totalPayments: 0,
          preFeePreDiscount: 0,
          couponReduction: 0,
          mealPlanDiscount: 0,
          salesTax: 0,
          processingFee: 0,
          deliveryFee: 0,
          purchasedGiftCardReduction: 0,
          gratuity: 0,
          coolerDeposit: 0,
          referralReduction: 0,
          promotionReduction: 0,
          pointsReduction: 0,
          chargedAmount: 0,
          preTransactionFeeAmount: 0,
          transactionFee: 0,
          amount: 0,
          refundedAmount: 0,
          balance: 0
        };

        payments.forEach(payment => {
          sums.totalPayments += 1;
          sums.preFeePreDiscount += payment.preFeePreDiscount;
          sums.couponReduction += payment.couponReduction;
          sums.mealPlanDiscount += payment.mealPlanDiscount;
          sums.salesTax += payment.salesTax;
          sums.processingFee += payment.processingFee;
          sums.deliveryFee += payment.deliveryFee;
          sums.purchasedGiftCardReduction += payment.purchasedGiftCardReduction;
          sums.gratuity += payment.gratuity;
          sums.coolerDeposit += payment.coolerDeposit;
          sums.referralReduction += payment.referralReduction;
          sums.promotionReduction += payment.promotionReduction;
          sums.pointsReduction += payment.pointsReduction;
          sums.chargedAmount += payment.chargedAmount;
          sums.preTransactionFeeAmount += payment.preTransactionFeeAmount;
          sums.transactionFee += payment.transactionFee;
          sums.amount += payment.amount;
          sums.refundedAmount += payment.refundedAmount;
          sums.balance += payment.balance;
        });

        payments.unshift({
          created_at: this.filters.byPaymentDate ? "TOTALS" : null,
          delivery_date: !this.filters.byPaymentDate ? "TOTALS" : null,
          totalPayments: totalPayments,
          preFeePreDiscount: sums.preFeePreDiscount,
          couponReduction: sums.couponReduction,
          mealPlanDiscount: sums.mealPlanDiscount,
          salesTax: sums.salesTax,
          processingFee: sums.processingFee,
          deliveryFee: sums.deliveryFee,
          purchasedGiftCardReduction: sums.purchasedGiftCardReduction,
          gratuity: sums.gratuity,
          coolerDeposit: sums.coolerDeposit,
          referralReduction: sums.referralReduction,
          promotionReduction: sums.promotionReduction,
          pointsReduction: sums.pointsReduction,
          preTransactionFeeAmount: sums.preTransactionFeeAmount,
          transactionFee: sums.transactionFee,
          chargedAmount: sums.chargedAmount,
          amount: sums.amount,
          refundedAmount: sums.refundedAmount,
          balance: sums.balance,
          sumRow: 1
        });
      }

      return payments;
    },
    columns() {
      let columns = ["created_at", "delivery_date"];
      if (this.filters.dailySummary) {
        columns.push("totalPayments");
      }
      columns.push("subtotal");

      let addedColumns = {};

      this.payments.forEach(payment => {
        if (payment.couponReduction > 0) addedColumns.couponReduction = true;
        if (payment.mealPlanDiscount > 0) addedColumns.mealPlanDiscount = true;
        if (payment.salesTax > 0) addedColumns.salesTax = true;
        if (payment.processingFee > 0) addedColumns.processingFee = true;
        if (payment.deliveryFee > 0) addedColumns.deliveryFee = true;
        if (payment.purchasedGiftCardReduction > 0)
          addedColumns.purchasedGiftCardReduction = true;
        if (payment.gratuity > 0) addedColumns.gratuity = true;
        if (payment.coolerDeposit > 0) addedColumns.coolerDeposit = true;
        if (payment.referralReduction > 0)
          addedColumns.referralReduction = true;
        if (payment.promotionReduction > 0)
          addedColumns.promotionReduction = true;
        if (payment.pointsReduction > 0) addedColumns.pointsReduction = true;
        if (payment.chargedAmount > 0) addedColumns.chargedAmount = true;
        if (payment.preTransactionFeeAmount > 0)
          addedColumns.preTransactionFeeAmount = true;
        if (payment.transactionFee > 0) addedColumns.transactionFee = true;
        if (payment.refundedAmount > 0) addedColumns.refundedAmount = true;
        if (payment.balance > 0) addedColumns.balance = true;
      });

      if (addedColumns.couponReduction) columns.push("couponReduction");
      if (addedColumns.mealPlanDiscount) columns.push("mealPlanDiscount");
      if (addedColumns.salesTax) columns.push("salesTax");
      if (addedColumns.processingFee) columns.push("processingFee");
      if (addedColumns.deliveryFee) columns.push("deliveryFee");
      if (addedColumns.purchasedGiftCardReduction)
        columns.push("purchasedGiftCardReduction");
      if (addedColumns.gratuity) columns.push("gratuity");
      if (addedColumns.coolerDeposit) columns.push("coolerDeposit");
      if (addedColumns.referralReduction) columns.push("referralReduction");
      if (addedColumns.promotionReduction) columns.push("promotionReduction");
      if (addedColumns.pointsReduction) columns.push("pointsReduction");
      if (addedColumns.chargedAmount) columns.push("chargedAmount");
      if (addedColumns.preTransactionFeeAmount)
        columns.push("preTransactionFeeAmount");
      if (addedColumns.transactionFee) columns.push("transactionFee");
      columns.push("amount");

      if (addedColumns.refundedAmount) columns.push("refundedAmount");

      if (addedColumns.balance) columns.push("balance");

      return columns;
    },
    coupons() {
      let coupons = [];
      if (this.storeCoupons.length > 0) {
        this.storeCoupons.forEach(coupon => {
          coupons.push({ text: coupon.code, value: coupon.id });
        });
      }
      return coupons;
    }
  },
  methods: {
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
      this.filters.startDate = this.filters.delivery_dates.start
        ? this.filters.delivery_dates.start
        : null;
      this.filters.endDate = this.filters.delivery_dates.end
        ? this.filters.delivery_dates.end
        : null;
      let params = this.filters;

      report = this.upcharges ? "upcharges" : report;

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
    submitMultiAuthPassword() {
      axios
        .post("/api/me/submitMultiAuthPassword", {
          password: this.multiAuthPassword
        })
        .then(resp => {
          if (resp.data == 1) {
            this.showMultiAuthModal = false;
          } else {
            this.$toastr.e("Incorrect password. Please try again.");
          }
        });
    },
    cancel() {
      this.$router.push({
        path: "/store/orders"
      });
    },
    clearDeliveryDates() {
      this.filters.delivery_dates.from = null;
      this.filters.delivery_dates.to = null;
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.$refs.deliveryDates.clearDates();
    },
    refreshPayments() {
      this.filters.storeId = this.store.id;
      this.filters.delivery_dates.from = this.filters.delivery_dates.start
        ? this.filters.delivery_dates.start
        : null;
      this.filters.delivery_dates.to = this.filters.delivery_dates.end
        ? this.filters.delivery_dates.end
        : null;

      axios
        .post("/api/me/getPayments", { filters: this.filters })
        .then(resp => {
          this.payments = resp.data;
        });
    }
  }
};
</script>
