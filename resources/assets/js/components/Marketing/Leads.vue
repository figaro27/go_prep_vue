<template>
  <div class="row">
    <div class="col-md-12">
      <img
        v-b-popover.hover="
          'Leads are users who created an account and viewed your menu one ore more times in the past but have not yet placed an order.'
        "
        title="Leads"
        src="/images/store/popover.png"
        class="popover-size mb-3"
      />
      <v-client-table :columns="columns" :data="tableData" :options="options">
        <span slot="beforeLimit">
          <b-btn variant="primary" @click="exportData('leads', 'pdf', true)">
            <i class="fa fa-print"></i>&nbsp; Print
          </b-btn>
          <b-dropdown class="mx-1" right text="Export as">
            <b-dropdown-item @click="exportData('leads', 'csv')"
              >CSV</b-dropdown-item
            >
            <b-dropdown-item @click="exportData('leads', 'xls')"
              >XLS</b-dropdown-item
            >
            <b-dropdown-item @click="exportData('leads', 'pdf')"
              >PDF</b-dropdown-item
            >
          </b-dropdown>
        </span>
        <div slot="created_at" slot-scope="props">
          <div>
            {{ moment(props.row.created_at).format("dddd, MMM Do") }}
          </div>
        </div>

        <div slot="total_paid" slot-scope="props">
          <div>{{ format.money(props.row.total_paid) }}</div>
        </div>

        <div slot="actions" class="text-nowrap" slot-scope="props">
          <button
            class="btn view btn-primary btn-sm"
            @click="viewCustomer(props.row.id)"
          >
            View Customer
          </button>
        </div>
      </v-client-table>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";
import format from "../../lib/format";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      columns: [
        "name",
        "email",
        "user_detail.phone",
        "user_detail.address",
        "user_detail.city",
        "user_detail.zip",
        "created_at"
      ],
      options: {
        headings: {
          Name: "Name",
          "user_detail.phone": "Phone",
          "user_detail.address": "Address",
          "user_detail.city": "City",
          "user_detail.zip": "Zip",
          created_at: "Account Created"
        },
        dateColumns: ["Joined"],
        customSorting: {
          TotalPaid: function(ascending) {
            return function(a, b) {
              var numA = parseInt(a.TotalPaid);
              var numB = parseInt(b.TotalPaid);
              if (ascending) return numA >= numB ? 1 : -1;
              return numA <= numB ? 1 : -1;
            };
          },
          Joined: function(ascending) {
            return function(a, b) {
              var numA = moment(a.Joined);
              var numB = moment(b.Joined);
              if (ascending) return numA.isBefore(numB, "day") ? 1 : -1;
              return numA.isAfter(numB, "day") ? 1 : -1;
            };
          },
          LastOrder: function(ascending) {
            return function(a, b) {
              var numA = moment(a.LastOrder);
              var numB = moment(b.LastOrder);
              if (ascending) return numA.isBefore(numB, "day") ? 1 : -1;
              return numA.isAfter(numB, "day") ? 1 : -1;
            };
          }
        },
        orderBy: {
          column: "name",
          ascending: true
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
      initialized: "initialized",
      leads: "storeLeads"
    }),
    tableData() {
      return this.leads;
    }
  },
  methods: {
    ...mapActions({}),
    formatMoney: format.money,
    exportData(report, format = "pdf", print = false) {
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
