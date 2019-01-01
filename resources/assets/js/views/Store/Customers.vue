<template>
  <div class="store-customer-container">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading"/>
        <div class="card">
          <ViewCustomer :userId="viewUserId"></ViewCustomer>
          <div class="card-header">Customers</div>
          <div class="card-body">
            <v-client-table
              :columns="columns"
              :data="tableData"
              :options="options"
              v-show="!isLoading"
            >
              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button class="btn btn-primary btn-sm" @click="viewUserId = props.row.id">View</button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
</style>
<script>
import Spinner from "../../components/Spinner";
import ViewCustomer from "./Modals/ViewCustomer";

export default {
  components: {
    Spinner,
    ViewCustomer
  },
  data() {
    return {
      isLoading: true,
      viewUserId: "",
      editUserId: "",
      id: "",
      columns: [
        "Name",
        "phone",
        "address",
        "city",
        "state",
        "Joined",
        "TotalPayments",
        "TotalPaid",
        "LastOrder",
        "actions"
      ],
      tableData: [],
      options: {
        headings: {
          LastOrder: "Last Order",
          TotalPayments: "Total Orders",
          TotalPaid: "Total Paid",
          Name: "Name",
          phone: "Phone",
          address: "Address",
          city: "City",
          state: "State",
          Joined: "Customer Since",
          actions: "Actions"
        },
        columnsDropdown: true,
        customSorting: {
          TotalPaid: function(ascending) {
            return function(a, b) {
              var numA = parseInt(a.TotalPaid);
              var numB = parseInt(b.TotalPaid);
              if (ascending) return numA >= numB ? 1 : -1;
              return numA <= numB ? 1 : -1;
            };
          }
        }
      }
    };
  },
  created() {},
  mounted() {
    this.getTableData();
  },
  methods: {
    getTableData() {
      axios.get("/api/me/customers").then(response => {
        this.tableData = response.data;
        this.isLoading = false;
      });
    },
    resetUserId() {
      this.viewUserId = 0;
      this.editUserId = 0;
    }
  }
};
</script>