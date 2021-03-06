<template>
  <div class="row">
    <div class="col-md-12">
      <img
        v-b-popover.hover="
          'Leads are users who viewed your menu one or more times in the past and created an account but have not yet placed an order.'
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
        <div slot="name" slot-scope="props">
          <div>{{ props.row.firstname }} {{ props.row.lastname }}</div>
        </div>
        <div slot="created_at" slot-scope="props">
          <div>
            {{ moment(props.row.created_at).format("dddd, MMM Do, YYYY") }}
          </div>
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
        "phone",
        "address",
        "city",
        "zip",
        "created_at"
      ],
      options: {
        headings: {
          Name: "Name",
          phone: "Phone",
          address: "Address",
          city: "City",
          zip: "Zip",
          created_at: "Menu Viewed"
        },
        customSorting: {
          TotalPaid: function(ascending) {
            return function(a, b) {
              var numA = parseInt(a.TotalPaid);
              var numB = parseInt(b.TotalPaid);
              if (ascending) return numA >= numB ? 1 : -1;
              return numA <= numB ? 1 : -1;
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
          column: "created_at",
          ascending: false
        }
      }
    };
  },
  created() {},
  mounted() {
    this.refreshLeads();
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      isLoading: "isLoading",
      initialized: "initialized",
      leads: "storeLeads"
    }),
    tableData() {
      return Object.values(this.leads);
    }
  },
  methods: {
    ...mapActions({
      refreshLeads: "refreshStoreLeads"
    }),
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
