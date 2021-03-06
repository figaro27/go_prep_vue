<template>
  <div>
    <b-alert
      style="background-color:#EBFAFF"
      show
      dismissible
      v-if="store.settings.payment_gateway !== 'stripe'"
    >
      <p class="strong">
        This area is available to Stripe stores only
      </p>
    </b-alert>
    <v-client-table
      :columns="columns"
      :options="options"
      :data="tableData"
      v-show="initialized"
    >
      <span slot="beforeLimit">
        <div class="d-flex">
          <b-btn variant="primary" @click="exportData('payouts', 'pdf', true)">
            <i class="fa fa-print"></i>&nbsp; Print Report
          </b-btn>
          <b-dropdown class="mx-1 mt-2 mt-sm-0" right text="Export as">
            <b-dropdown-item @click="exportData('payouts', 'csv')"
              >CSV</b-dropdown-item
            >
            <b-dropdown-item @click="exportData('payouts', 'xls')"
              >XLS</b-dropdown-item
            >
            <b-dropdown-item @click="exportData('payouts', 'pdf')"
              >PDF</b-dropdown-item
            >
          </b-dropdown>
        </div>
      </span>
      <div slot="beforeTable" class="mb-2">
        <div class="table-before d-flex flex-wrap align-items-center">
          <delivery-date-picker
            v-model="filters.dates"
            @change="onChangeDateFilter"
            class="mt-3 mt-sm-0"
            ref="deliveryDates"
            :regularDate="true"
          ></delivery-date-picker>
          <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>
        </div>
      </div>
      <div slot="type" slot-scope="props">
        <p v-if="props.row.amount > 0">Payout</p>
        <p v-else>
          Withdrawal
          <img
            v-b-popover.hover="
              'Your available Stripe balance may go into the negative if the cost of refunds or disputes is greater than the existing balance. As a result, funds are withdrawn back from your bank account.'
            "
            title="Withdrawal"
            src="/images/store/popover.png"
            class="popover-size ml-1"
          />
        </p>
      </div>
      <div slot="created" slot-scope="props">
        {{ moment(props.row.created).format("dddd, MMM Do") }}
      </div>
      <div slot="arrival_date" slot-scope="props">
        {{ moment(props.row.arrival_date).format("dddd, MMM Do") }}
      </div>
      <div slot="status" slot-scope="props">
        <p
          v-if="
            props.row.status === 'in_transit' ||
              props.row.status === 'In_transit'
          "
        >
          In Transit
        </p>
        <p v-else>{{ props.row.status }}</p>
      </div>
      <div slot="amount" slot-scope="props">
        {{ format.money(props.row.amount, store.settings.currency) }}
      </div>
      <div slot="actions" slot-scope="props">
        <button
          class="btn view btn-primary btn-sm"
          @click="viewPayout(props.row)"
        >
          View
        </button>
      </div>
    </v-client-table>
    <b-modal size="xl" v-model="transactionsModal" hide-header>
      <div v-if="selectedPayout" class="d-flex d-inline mt-4 mb-1">
        <p class="mr-4 font-18">
          <strong>Initiated:</strong>
          {{ moment(selectedPayout.created).format("dddd, MMM Do") }}
        </p>
        <p class="mr-4 font-18">
          <strong>Arrival Date:</strong>
          {{ moment(selectedPayout.arrival_date).format("dddd, MMM Do") }}
        </p>
        <p class="mr-4 font-18">
          <strong>Total:</strong>
          {{ format.money(selectedPayout.amount, store.settings.currency) }}
        </p>
        <p class="font-18">
          <strong>Status:</strong>
          {{
            selectedPayout.status.charAt(0).toUpperCase() +
              selectedPayout.status.slice(1)
          }}
        </p>
      </div>

      <v-client-table
        :columns="transactionsColumns"
        :options="transactionsOptions"
        :data="transactionsTableData"
        ref="v-table"
      >
        <span slot="beforeLimit">
          <div class="d-flex">
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
          {{ moment(props.row.created_at).format("dddd, MMM Do") }}
        </div>
        <div slot="amount" slot-scope="props">
          {{ format.money(props.row.amount, store.settings.currency) }}
        </div>
      </v-client-table>
    </b-modal>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import format from "../../lib/format";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import store from "../../store";
import { createInstance } from "vuex-pagination";

export default {
  components: {
    Spinner,
    vSelect
  },
  watch: {
    tabs(val) {
      if (val == 1) {
        this.refreshTableData();
      }
    }
  },
  mixins: [],
  props: {
    tabs: null
  },
  data() {
    return {
      tableData: [],
      columns: [
        "created",
        "type",
        "status",
        "bank_name",
        "arrival_date",
        "amount",
        "actions"
      ],
      options: {
        headings: {
          created: "Payout Date",
          bank_name: "Bank",
          arrival_date: "Arrival Date"
        }
      },
      filters: {
        startDate: null,
        endDate: null,
        dates: {
          start: null,
          end: null
        }
      },
      transactionsModal: false,
      transactions: null,
      selectedPayout: null,
      transactionsColumns: [
        "customer",
        "order_number",
        "created_at",
        "type",
        "amount"
      ],
      transactionsOptions: {
        headings: {
          created_at: "Paid",
          type: "Type",
          customer: "Customer",
          order_number: "Order ID",
          amount: "Amount"
        }
      }
    };
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized"
    }),
    transactionsTableData() {
      return this.transactions
        ? this.transactions.sort((a, b) => {
            return a.created - b.created;
          })
        : [];
    }
  },
  methods: {
    refreshTableData() {
      axios.get("/api/me/payouts").then(resp => {
        this.tableData = resp.data;
      });
    },
    onChangeDateFilter() {
      axios
        .post("/api/me/getPayoutsWithDates", {
          start_date: this.filters.dates.start
            ? this.filters.dates.start
            : null,
          end_date: this.filters.dates.end ? this.filters.dates.end : null
        })
        .then(resp => {
          this.tableData = resp.data;
        });
    },
    clearDeliveryDates() {
      this.filters.dates.start = null;
      this.filters.dates.end = null;
      this.$refs.deliveryDates.clearDates();
      this.refreshTableData();
    },
    viewPayout(payout) {
      this.$refs["v-table"].setFilter("");
      this.selectedPayout = payout;
      axios.post("/api/me/getBalanceHistory", { payout: payout }).then(resp => {
        this.transactions = resp.data;
        this.transactionsModal = true;
      });
    },
    async exportData(report, format = "pdf", print = false) {
      let params = this.filters;

      this.filters.startDate = this.filters.dates.start
        ? moment(this.filters.dates.start).format("YYYY-MM-DD")
        : null;
      this.filters.endDate = this.filters.dates.end
        ? moment(this.filters.dates.end).format("YYYY-MM-DD")
        : null;

      params.payoutId = this.selectedPayout.id;

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
    }
  }
};
</script>
