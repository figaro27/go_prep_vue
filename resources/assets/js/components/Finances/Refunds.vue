<template>
  <div>
    <v-client-table
      :columns="columns"
      :options="options"
      :data="tableData"
      v-show="initialized"
    >
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
      <div slot="created_at" slot-scope="props">
        {{ moment(props.row.created_at).format("dddd, MMM Do") }}
      </div>
      <div slot="amount" slot-scope="props">
        {{ format.money(props.row.amount, store.settings.currency) }}
      </div>
    </v-client-table>
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
      if (val == 2) {
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
      columns: ["created_at", "order", "customer", "card", "amount"],
      options: {
        headings: {
          created_at: "Refund Date",
          order: "Order ID"
        }
      },
      filters: {
        dates: {
          start: null,
          end: null
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
    })
  },
  methods: {
    refreshTableData() {
      axios.get("/api/me/refunds").then(resp => {
        this.tableData = resp.data.map(record => {
          return {
            created_at: record.created_at,
            customer:
              record.user.user_detail.firstname +
              " " +
              record.user.user_detail.lastname,
            order: record.order_number,
            card: record.card.brand + " " + record.card.last4,
            amount: record.amount
          };
        });
      });
    },
    onChangeDateFilter() {
      axios
        .post("/api/me/getRefundsWithDates", {
          start_date: this.filters.dates.start
            ? this.filters.dates.start
            : null,
          end_date: this.filters.dates.end ? this.filters.dates.end : null
        })
        .then(resp => {
          this.tableData = resp.data;
          // this.tableData = resp.data.map(record => {
          //   return {
          //     created_at: record.created_at,
          //     reason: this.getErrorCode(record.error),
          //     customer:
          //       record.user.user_detail.firstname +
          //       " " +
          //       record.user.user_detail.lastname
          //   };
          // });
        });
    },
    clearDeliveryDates() {
      this.filters.dates.start = null;
      this.filters.dates.end = null;
      this.$refs.deliveryDates.clearDates();
      this.refreshTableData();
    }
  }
};
</script>
