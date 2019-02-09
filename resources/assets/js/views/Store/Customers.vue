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
        <div class="row light-background border-bottom mb-3">
          <div class="col-md-4 pt-4">
            <h4>Customer</h4>
            <p>{{ customer.name }}</p>

            <h4>Phone</h4>
            <p>{{ customer.phone }}</p>
          </div>
          <div class="col-md-4 pt-4">
            <h4>Address</h4>
            <p>{{ customer.address }}</p>
            <p>{{ customer.city }}, {{ customer.state }}</p>
            <p>{{ customer.zip }}</p>
          </div>
          <div class="col-md-4 pt-4">
            <h4>Delivery Instructions</h4>
            <p>{{ customer.delivery }}</p>
          </div>
        </div>
        <div v-for="order in customer.orders" :key="`order-${order.id}`">
          <div v-b-toggle="'collapse' + order.id">
            <b-list-group-item>
              <div class="row">
                <div class="col-md-4">
                  <h4>Order ID</h4>
                  <p>{{ order.order_number }}</p>
                </div>
                <div class="col-md-4">
                  <h4>Placed On</h4>
                  <p>{{ moment(order.created_at).format('dddd, MMM Do, Y') }}</p>
                </div>
                <div class="col-md-3">
                  <h2>{{ format.money(order.amount) }}</h2>
                </div>
                <div class="col-md-1">
                  <img class="pull-right mt-2" src="/images/collapse-arrow.png">
                </div>
              </div>

              <b-collapse :id="'collapse' + order.id" class="mt-2">
                    <ul class="meal-quantities">
                      <li v-for="mealId in order.meal_ids" :key="$uuid.v1()">
                        <div class="row">
                          <div class="col-md-5 pr-0">
                            <span class="order-quantity">{{ order.meal_quantities[mealId] }}</span>
                            <img src="/images/store/x-modal.png" class="mr-2 ml-2">
                            <img :src="meal(mealId).featured_image" class="modalMeal" />
                          </div>
                          <div class="col-md-7 pt-3 nopadding">
                            <p>{{ meal(mealId).title }}</p>
                            <p>{{ format.money(meal(mealId).price) }}</p>
                          </div>
                        </div>                       
                      </li>
                    </ul>

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
        "name",
        "phone",
        "address",
        "city",
        "zip",
        "first_order",
        "total_payments",
        "total_paid",
        "last_order",
        "actions"
      ],
      options: {
        headings: {
          last_order: "Last Order",
          total_payments: "Total Orders",
          total_paid: "Total Paid",
          Name: "Name",
          phone: "Phone",
          address: "Address",
          city: "City",
          zip: "Zip",
          first_order: "Customer Since",
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
    },
    getMealQuantities(meals) {
      let order = _.toArray(_.countBy(meals, "id"));

      return order.map((order, id) => {
        return {
          order,
          featured_image: meals[id].featured_image,
          title: meals[id].title,
          price: meals[id].price
        };
      });
    },
  }
};
</script>