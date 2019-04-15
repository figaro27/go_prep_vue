<template>
  <div class="store-customer-container">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading" />
        <div class="card">
          <div class="card-body">
            <v-client-table
              :columns="columns"
              :data="tableData"
              :options="options"
              v-show="!isLoading"
            >
              <span slot="beforeLimit">
                <b-btn
                  variant="primary"
                  @click="exportData('customers', 'pdf', true)"
                >
                  <i class="fa fa-print"></i>&nbsp; Print
                </b-btn>
                <b-dropdown class="mx-1" right text="Export as">
                  <b-dropdown-item @click="exportData('customers', 'csv')"
                    >CSV</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('customers', 'xls')"
                    >XLS</b-dropdown-item
                  >
                  <b-dropdown-item @click="exportData('customers', 'pdf')"
                    >PDF</b-dropdown-item
                  >
                </b-dropdown>
              </span>

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
        <div
          v-for="order in customer.paid_orders"
          :key="`order-${order.order_number}`"
        >
          <div v-b-toggle="'collapse' + order.order_number">
            <b-list-group-item>
              <div class="row">
                <div class="col-md-4">
                  <h4>Order ID</h4>
                  <p>{{ order.order_number }}</p>
                </div>
                <div class="col-md-4">
                  <h4>Placed On</h4>
                  <p>
                    {{
                      moment
                        .utc(order.created_at)
                        .local()
                        .format("dddd, MMM Do, Y")
                    }}
                  </p>
                </div>
                <div class="col-md-3">
                  <h2>{{ format.money(order.amount) }}</h2>
                </div>
                <div class="col-md-1">
                  <img
                    class="pull-right mt-2"
                    src="/images/collapse-arrow.png"
                  />
                </div>
              </div>

              <b-collapse :id="'collapse' + order.order_number" class="mt-2">
                <ul class="meal-quantities">
                  <li
                    v-for="meal in getMealQuantities(order)"
                    :key="$uuid.v1()"
                  >
                    <div class="row">
                      <div class="col-md-5 pr-0">
                        <span class="order-quantity">
                          {{ meal.quantity }}
                        </span>
                        <img
                          src="/images/store/x-modal.png"
                          class="mr-2 ml-2"
                        />
                        <thumbnail
                          v-if="meal.image.url_thumb"
                          :src="meal.image.url_thumb"
                          :spinner="false"
                        ></thumbnail>
                      </div>
                      <div class="col-md-7 pt-3 nopadding">
                        <p>{{ meal.title }}</p>
                        <p>{{ format.money(meal.item_price) }}</p>
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
  computed: {
    ...mapGetters({
      store: "viewedStore",
      meal: "storeMeal",
      getMeal: "storeMeal",
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
      });
    },
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
    },
    getMealQuantities(order) {
      let mealCounts = {};

      _.forEach(order.meal_quantities, (quantity, mealId) => {
        if (!mealCounts[mealId]) {
          mealCounts[mealId] = 0;
        }
        mealCounts[mealId] += quantity;
      });

      return _.map(mealCounts, (quantity, mealId) => {
        let mealIdParts = mealId.split("-"); // mealId-sizeId
        let meal = this.getMeal(mealIdParts[0]);
        let size = null;
        let title = null;
        let price = meal.price;

        if (mealIdParts[1]) {
          size = meal.getSize(mealIdParts[1]);
          title = size.full_title;
          price = size.price;
        } else {
          title = meal.item_title;
        }

        return {
          ...meal,
          title,
          price,
          size,
          quantity: quantity,
          total: quantity * price
        };
      });

      if (!_.isArray(meals)) {
        return [];
      }
      return meals.map((meal, id) => {
        return {
          quantity: meal.item_quantity,
          image: meal.image,
          title: meal.item_title,
          price: meal.item_price
        };
      });
    }
  }
};
</script>
