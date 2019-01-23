<template>
  <div class="store-customer-container">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading"/>
        <div class="card">
          <div class="card-body">
            <v-client-table
              :columns="columns"
              :data="tableData"
              :options="options"
              v-show="!isLoading"
            >
              <span slot="beforeLimit">
                <b-btn variant="primary" @click="exportData('customers', 'pdf', true)">
                  <i class="fa fa-print"></i>&nbsp;
                  Print
                </b-btn>
                <b-dropdown class="mx-1" right text="Export as">
                  <b-dropdown-item @click="exportData('customers', 'csv')">CSV</b-dropdown-item>
                  <b-dropdown-item @click="exportData('customers', 'xls')">XLS</b-dropdown-item>
                  <b-dropdown-item @click="exportData('customers', 'pdf')">PDF</b-dropdown-item>
                </b-dropdown>
              </span>

              <div slot="total_paid" slot-scope="props">
                <div>{{ format.money(props.row.total_paid) }}</div>
              </div>

              <div slot="actions" class="text-nowrap" slot-scope="props">
                <button
                  class="btn view btn-primary btn-sm"
                  @click="viewCustomer(props.row.id)"
                >View Customer</button>
              </div>
            </v-client-table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-basic">
      <b-modal
        size="lg"
        title="Customer Details"
        v-model="viewCustomerModal"
        v-if="viewCustomerModal"
      >
        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <p>{{ customer.user_detail.firstname }} {{ customer.user_detail.lastname }}</p>

            <h4>Phone</h4>
            <p>{{ customer.user_detail.phone }}</p>
          </div>
          <div class="col-md-4">
            <h4>Address</h4>
            <p>{{ customer.user_detail.address }}</p>
            <p>{{ customer.user_detail.city }}, {{ customer.user_detail.state }}</p>
            <p>{{ customer.user_detail.zip }}</p>
          </div>
          <div class="col-md-4">
            <h4>Delivery Instructions</h4>
            <p>{{ customer.user_detail.delivery }}</p>
          </div>
        </div>

        <hr>
        <div v-for="order in orders" :key="order.id">
          <div v-b-toggle="'collapse' + order.id">
            <b-list-group-item>
              <div class="row">
                <div class="col-md-4">
                  <h4>Order ID</h4>
                  <p>{{ order.order_number }}</p>
                </div>
                <div class="col-md-4">
                  <h4>Placed On</h4>
                  <p>{{ order.created_at }}</p>
                </div>
                <div class="col-md-4">
                  <h2>{{ format.money(order.amount) }}</h2>
                </div>
              </div>

              <!-- Need to fix this bug & get meal quantities -->
              <b-collapse :id="'collapse' + order.id" class="mt-2">
                <b-card>
                  <p class="card-text">
                    <div v-for="mealId in order.meal_ids" :key="mealId">
                      <img :src="meal(mealId).featured_image" class="modalMeal" />
                      {{ meal(mealId).title }}
                      {{ format.money(meal(mealId).price) }}
                    </div>
                  </p>
                </b-card>
              </b-collapse>
            </b-list-group-item>
          </div>
        </div>
      </b-modal>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
// import ViewCustomer from "./Modals/ViewCustomer";
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  components: {
    Spinner
    // ViewCustomer
  },
  data() {
    return {
      viewCustomerModal: false,
      userId: "",
      editUserId: "",
      id: "",
      customer: [],
      orders: [],
      columns: [
        "Name",
        "phone",
        "address",
        "city",
        "state",
        "Joined",
        "total_payments",
        "total_paid",
        "LastOrder",
        "actions"
      ],
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
        dateColumns: ['Joined'],
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
              if (ascending) return numA.isBefore(numB, 'day') ? 1 : -1;
              return numA.isAfter(numB, 'day') ? 1 : -1;
            };
          },
          LastOrder: function(ascending) {
            return function(a, b) {
              var numA = moment(a.LastOrder);
              var numB = moment(b.LastOrder);
              if (ascending) return numA.isBefore(numB, 'day') ? 1 : -1;
              return numA.isAfter(numB, 'day') ? 1 : -1;
            };
          },
        }
      }
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      meal: "storeMeal",
      customers: "storeCustomers",
      //orders: "storeOrders",
      isLoading: "isLoading",
      _storeOrdersByCustomer: "storeOrdersByCustomer"
    }),
    tableData() {
      return Object.values(this.customers);
    },
    customerOrders() {
      let orders = this.userId ? this._storeOrdersByCustomer(this.userId) : [];
    }
  },
  created() {},
  mounted() {},
  methods: {
    resetUserId() {
      this.userId = 0;
      this.editUserId = 0;
    },
    viewCustomer(id) {
      this.userId = id;
      this.viewCustomerModal = true;
      axios.get(`/api/me/customers/${id}`).then(response => {
        this.customer = response.data;
        this.orders = response.data.order;
      });
    },
    exportData(report, format = "pdf", print = false) {
      axios
        .get(`/api/me/print/${report}/${format}`)
        .then(response => {
          if (!_.isEmpty(response.data.url)) {
            let win = window.open(response.data.url);
            if(print) {
              win.addEventListener('load', () => {
                win.print();
              }, false);
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