<template>
  <div class="store-customer-container mt-3">
    <div class="row">
      <div class="col-md-12">
        <Spinner v-if="isLoading" />
        <add-customer-modal :addCustomerModal="addCustomerModal">
        </add-customer-modal>

        <add-customer-modal v-model="addCustomerModal" v-if="addCustomerModal">
        </add-customer-modal>

        <div class="card">
          <div class="card-body">
            <v-client-table
              :columns="columns"
              :data="tableData"
              :options="options"
              v-show="!isLoading"
            >
              <div slot="beforeTable" class="mb-2">
                <button
                  v-if="storeModules.manualCustomers"
                  class="btn btn-success btn-md mb-2 mb-sm-0"
                  @click="addCustomerModal = true"
                >
                  Add Customer
                </button>
              </div>
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
        no-fade
      >
        <div class="row light-background border-bottom mb-3">
          <div class="col-md-4 pt-4">
            <h4>Customer</h4>
            <p>{{ customer.name }}</p>

            <h4>Phone</h4>
            <p>{{ customer.phone }}</p>

            <h4>Email</h4>
            <p>{{ customer.email }}</p>
          </div>
          <div class="col-md-4 pt-4">
            <h4>Address</h4>
            <p>{{ customer.address }}</p>
            <p>{{ customer.city }}, {{ customer.state }} {{ customer.zip }}</p>
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
                  <h4 v-if="storeModules.dailyOrderNumbers">Daily Order #</h4>
                  <p v-if="storeModules.dailyOrderNumbers">
                    {{ order.dailyOrderNumber }}
                  </p>
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
                <div class="col-md-4">
                  <span
                    >Subtotal:
                    {{
                      format.money(order.preFeePreDiscount, order.currency)
                    }}</span
                  >
                  <br />
                  <span v-if="order.mealPlanDiscount > 0">
                    Subscription Discount:
                    <span class="text-success"
                      >({{
                        format.money(order.mealPlanDiscount, order.currency)
                      }})</span
                    >
                    <br />
                  </span>
                  <span v-if="order.salesTax > 0"
                    >Sales Tax:
                    {{ format.money(order.salesTax, order.currency) }}</span
                  >
                  <br />
                  <span v-if="order.deliveryFee > 0">
                    Delivery Fee:
                    {{ format.money(order.deliveryFee, order.currency) }}
                    <br />
                  </span>
                  <span v-if="order.processingFee > 0">
                    Processing Fee:
                    {{ format.money(order.processingFee, order.currency) }}
                    <br />
                  </span>

                  <span>
                    <strong>
                      <span v-if="order.couponReduction === null"
                        >Total:
                        {{ format.money(order.amount, order.currency) }}</span
                      >
                    </strong>
                    <div v-if="order.couponReduction > 0">
                      Pre-Coupon Total:
                      {{ format.money(order.pre_coupon, order.currency) }}
                      <br />
                      <span class="text-success">
                        (Coupon {{ order.couponCode }}:
                        {{
                          format.money(order.couponReduction, order.currency)
                        }})
                      </span>
                      <br />
                      <strong
                        >Total:
                        {{ format.money(order.amount, order.currency) }}</strong
                      >
                    </div>
                  </span>
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
                        <span class="order-quantity">{{ meal.quantity }}</span>
                        <img
                          src="/images/store/x-modal.png"
                          class="mr-2 ml-2"
                        />
                        <thumbnail
                          v-if="meal.image"
                          :src="meal.image"
                          :spinner="false"
                        ></thumbnail>
                      </div>
                      <div class="col-md-7 pt-3 nopadding">
                        <p v-html="meal.title"></p>
                        <p>{{ format.money(meal.subtotal, order.currency) }}</p>
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
import format from "../../lib/format";
import { mapGetters, mapActions, mapMutations } from "vuex";
import states from "../../data/states.js";
import AddCustomerModal from "../../components/Customer/AddCustomerModal";

export default {
  components: {
    Spinner,
    AddCustomerModal
    // ViewCustomer
  },
  data() {
    return {
      form: {},
      addCustomerModal: false,
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
      _storeOrdersByCustomer: "storeOrdersByCustomer",
      storeModules: "storeModules"
    }),
    stateNames() {
      return states.selectOptions("US");
    },
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
    ...mapActions({
      refreshStoreCustomers: "refreshStoreCustomers"
    }),
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
      let data = order.items.map(item => {
        const meal = this.getMeal(item.meal_id);
        if (!meal) {
          return null;
        }

        const size = meal.getSize(item.meal_size_id);
        const title = meal.getTitle(
          true,
          size,
          item.components,
          item.addons,
          item.special_instructions
        );

        let image = null;
        if (meal.image != null) image = meal.image.url_thumb;

        return {
          image: image,
          title: title,
          quantity: item.quantity,
          unit_price: format.money(item.unit_price, order.currency),
          subtotal: format.money(item.price, order.currency)
        };
      });

      return _.filter(data);
    }
  }
};
</script>
