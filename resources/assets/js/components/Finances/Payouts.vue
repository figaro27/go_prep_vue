<template>
  <div>
    <div class="row mt-3">
      <div class="col-md-12">
        <v-client-table
          :columns="payoutColumns"
          :options="payoutOptions"
          :data="payoutTableData"
          v-show="initialized"
          @pagination="onChangePage"
          class="table-countless"
        >
          <span slot="beforeLimit">
            <div class="d-flex">
              <!-- <b-form-checkbox v-model="filters.includeTransactions" class="mr-2 pt-1">Include Transactions</b-form-checkbox> -->
              <b-btn
                variant="primary"
                @click="exportData('payouts', 'pdf', true)"
              >
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

          <div slot="bank" slot-scope="props">
            {{ bank }}
          </div>
          <div slot="created" slot-scope="props">
            {{ moment.unix(props.row.created).format("dddd, MMM Do") }}
          </div>
          <div slot="arrival_date" slot-scope="props">
            {{ moment.unix(props.row.arrival_date).format("dddd, MMM Do") }}
          </div>
          <div slot="amount" slot-scope="props">
            {{ format.money(props.row.amount / 100, store.settings.currency) }}
          </div>
          <div slot="status" slot-scope="props">
            {{
              props.row.status.charAt(0).toUpperCase() +
                props.row.status.slice(1)
            }}
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
        <div class="text-center">
          <b-pagination
            v-model="payouts.page"
            :total-rows="payouts.total"
            :per-page="10"
            align="center"
            :hide-ellipsis="true"
          ></b-pagination>
          {{ payouts.total }} Records
        </div>
      </div>
    </div>

    <b-modal size="xl" v-model="transactionsModal" hide-header>
      <div v-if="selectedPayout" class="d-flex d-inline mt-4 mb-1">
        <p class="mr-4 font-18">
          <strong>Initiated:</strong>
          {{ moment.unix(selectedPayout.created).format("dddd, MMM Do") }}
        </p>
        <p class="mr-4 font-18">
          <strong>Arrival Date:</strong>
          {{ moment.unix(selectedPayout.arrival_date).format("dddd, MMM Do") }}
        </p>
        <p class="mr-4 font-18">
          <strong>Total:</strong>
          {{
            format.money(selectedPayout.amount / 100, store.settings.currency)
          }}
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
      >
        <div slot="created_at" slot-scope="props">
          {{ moment(props.row.created_at.date).format("dddd, MMM Do") }}
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
  mixins: [],
  props: {
    tabs: null
  },
  data() {
    return {
      bank: null,
      transactionsModal: false,
      transactions: null,
      // payouts: null,
      selectedPayout: null,
      filters: {
        includeTransactions: false,
        dates: {
          start: null,
          end: null
        }
      },
      payoutColumns: [
        "bank",
        "created",
        "arrival_date",
        "amount",
        "status",
        "actions"
      ],
      payoutOptions: {
        headings: {
          bank: "Bank",
          created: "Intitiated",
          amount: "Total",
          arrival_date: "Arrival Date"
        }
      },
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
  watch: {
    tabs(val) {
      if (val == 2) {
        this.refreshResource("payouts");
        // this.getPayoutTableData();
      }
    }
  },
  created() {},
  mounted() {},
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized"
    }),
    payouts: createInstance("payouts", {
      page: 1,
      pageSize: 10,
      args() {
        let args = {
          start: this.filters.dates.start || null,
          end: this.filters.dates.end || null
        };

        return args;
      }
    }),
    payoutTableData() {
      return this.payouts.items ? this.payouts.items : [];
    },
    transactionsTableData() {
      return this.transactions
        ? this.transactions.sort((a, b) => {
            return a.created - b.created;
          })
        : [];
    }
  },
  methods: {
    ...mapActions("resources", ["refreshResource"]),
    getPayoutTableData() {
      axios.get("/api/me/payouts").then(resp => {
        this.bank = resp.data.bank;
        this.payouts = resp.data.data;
      });
    },
    viewPayout(payout) {
      this.selectedPayout = payout;
      axios.post("/api/me/getBalanceHistory", { payout: payout }).then(resp => {
        this.transactions = resp.data;
        this.transactionsModal = true;
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
          this.bank = resp.data.bank;
          this.payouts = resp.data.data;
        });
    },
    clearDeliveryDates() {
      this.filters.dates.start = null;
      this.filters.dates.end = null;
      this.$refs.deliveryDates.clearDates();
      this.getPayoutTableData();
    },
    onChangePage(page) {
      this.payouts.page = page;
    },
    async exportData(report, format = "pdf", print = false) {
      // const warning = this.checkDateRange({ ...this.filters.dates });

      let params = {};
      if (this.filters.dates.start) {
        params.start_date = moment(this.filters.dates.start).format(
          "MMMM Do YYYY"
        );
        params.end_date = this.filters.dates.end
          ? moment(this.filters.dates.end).format("MMMM Do YYYY")
          : null;
      }

      params.includeTransactions = this.filters.includeTransactions;

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
