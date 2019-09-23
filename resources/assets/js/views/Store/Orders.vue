<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading" />
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
            v-show="initialized"
          >
            <div slot="beforeTable" class="mb-2">
              <div class="table-before d-flex flex-wrap align-items-center">
                <div class="d-inline-block mb-2 mb-md-0 mr-2 flex-grow-0">
                  <!-- <b-btn
                    @click="$set(filters, 'has_notes', !filters.has_notes)"
                    :selected="filters.has_notes"
                    variant="primary"
                    class="filter-btn"
                    >Filter Notes</b-btn
                  > -->
                </div>
                <div class="d-inline-block mr-2 flex-grow-0">
                  <!-- <b-btn
                    @click="showFulfilledOrders()"
                    :selected="filters.fulfilled"
                    variant="warning"
                    class="filter-btn"
                    v-if="!filters.fulfilled"
                    >View Completed Orders</b-btn
                  >
                  <b-btn
                    @click="showUnfulfilledOrders()"
                    :selected="filters.fulfilled"
                    variant="danger"
                    class="filter-btn"
                    v-if="filters.fulfilled"
                    >View Open Orders</b-btn
                  > -->
                  <router-link
                    to="/store/manual-order"
                    v-if="storeModules.manualOrders"
                  >
                    <b-btn class="btn btn-success filter-btn"
                      >Create Manual Order</b-btn
                    >
                  </router-link>
                </div>
                <delivery-date-picker
                  v-model="filters.delivery_dates"
                  @change="onChangeDateFilter"
                  class="mt-3 mt-sm-0"
                  ref="deliveryDates"
                ></delivery-date-picker>
                <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>
              </div>
            </div>

            <span slot="beforeLimit">
              <b-btn
                variant="warning"
                @click="exportData('packing_slips', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print Packing Slips
              </b-btn>
              <b-btn
                variant="success"
                @click="exportData('orders_by_customer', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print Orders Summary
              </b-btn>
              <b-btn
                variant="primary"
                @click="exportData('orders', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print Orders
              </b-btn>
              <b-dropdown class="mx-1 mt-2 mt-sm-0" right text="Export as">
                <b-dropdown-item @click="exportData('orders', 'csv')"
                  >CSV</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('orders', 'xls')"
                  >XLS</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('orders', 'pdf')"
                  >PDF</b-dropdown-item
                >
              </b-dropdown>
            </span>

            <div slot="notes" class="text-nowrap" slot-scope="props">
              <p v-if="props.row.has_notes">
                <img src="/images/store/note.png" />
              </p>
            </div>
            <div slot="created_at" slot-scope="props">
              {{ moment(props.row.created_at).format("dddd, MMM Do") }}
            </div>
            <div slot="delivery_date" slot-scope="props">
              {{ moment(props.row.delivery_date).format("dddd, MMM Do") }}
            </div>
            <div slot="pickup" slot-scope="props">
              {{ props.row.pickup ? "Pickup" : "Delivery" }}
            </div>
            <div slot="dailyOrderNumber" slot-scope="props">
              {{ props.row.dailyOrderNumber }}
            </div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button
                class="btn view btn-primary btn-sm"
                @click="viewOrder(props.row.id)"
              >
                View Order
              </button>
              <button
                v-if="props.row.deposit != 100"
                class="btn view btn-success btn-sm"
                @click="chargeBalance(props.row.id)"
              >
                Charge {{ 100 - props.row.deposit }}% Balance
              </button>
              <!-- <b-btn
                v-if="!props.row.fulfilled"
                class="btn btn-primary btn-sm"
                @click="fulfill(props.row.id)"
                variant="primary"
                >Mark As Complete</b-btn
              >
              <b-btn
                v-else
                class="btn btn-primary btn-sm"
                @click="unfulfill(props.row.id)"
                variant="danger"
                >Unmark As Complete</b-btn
              > -->
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>

    <div class="modal-basic modal-wider">
      <b-modal
        v-model="viewOrderModal"
        size="lg"
        title="Order Information"
        no-fade
      >
        <div class="row light-background" v-if="order.adjusted">
          <div class="col-md-12">
            <p class="center-text red pt-3">
              The items and/or delivery date of this order was adjusted.
            </p>
          </div>
        </div>
        <div class="row light-background border-bottom mb-3">
          <div class="col-md-4 pt-1">
            <span v-if="storeModules.dailyOrderNumbers">
              <h4>Order #</h4>
              <p>{{ order.dailyOrderNumber }}</p>
            </span>
            <h4>Order ID</h4>
            <p>{{ order.order_number }}</p>

            <router-link
              :to="{ name: 'store-adjust-order', params: { orderId: orderId } }"
            >
              <b-btn class="btn btn-success mb-2">Adjust Order</b-btn>
            </router-link>
          </div>
          <div class="col-md-4 pt-1">
            <h4>Placed On</h4>
            <p>{{ moment(order.created_at).format("dddd, MMM Do") }}</p>
          </div>
          <div class="col-md-4 pt-1">
            <h4 v-if="order.cashOrder">Cash Order</h4>
            <p>
              Subtotal:
              {{ format.money(order.preFeePreDiscount, order.currency) }}
            </p>
            <p class="text-success" v-if="order.couponReduction > 0">
              Coupon {{ order.couponCode }}: ({{
                format.money(order.couponReduction, order.currency)
              }})
            </p>
            <p v-if="order.mealPlanDiscount > 0" class="text-success">
              Subscription Discount: ({{
                format.money(order.mealPlanDiscount, order.currency)
              }})
            </p>
            <p v-if="order.deliveryFee > 0">
              Delivery Fee:
              {{ format.money(order.deliveryFee, order.currency) }}
            </p>
            <p v-if="order.processingFee > 0">
              Processing Fee:
              {{ format.money(order.processingFee, order.currency) }}
            </p>
            <p v-if="order.salesTax > 0">
              Sales Tax: {{ format.money(order.salesTax, order.currency) }}
            </p>
            <p class="strong">
              Total: {{ format.money(order.amount, order.currency) }}
            </p>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <p>{{ user_detail.firstname }} {{ user_detail.lastname }}</p>
          </div>
          <div class="col-md-4">
            <h4>Address</h4>
            <p>
              {{ user_detail.address }}<br />
              {{ user_detail.city }}, {{ user_detail.state }}
              {{ user_detail.zip }}
            </p>
          </div>
          <div class="col-md-4">
            <h4 v-if="!order.pickup">Delivery Day</h4>
            <h4 v-if="order.pickup">Pickup Day</h4>
            {{ moment(order.delivery_date).format("dddd, MMM Do") }}
            <span v-if="order.transferTime"> - {{ order.transferTime }}</span>
            <p v-if="order.pickup_location_id != null">
              {{ order.pickup_location.name }}<br />
              {{ order.pickup_location.address }},
              {{ order.pickup_location.city }},
              {{ order.pickup_location.state }}
              {{ order.pickup_location.zip }}
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <h4>Phone</h4>
            <p>{{ user_detail.phone }}</p>
          </div>
          <div class="col-md-4">
            <h4>Email</h4>
            <p>
              {{ email }}
            </p>
          </div>
          <div class="col-md-4">
            <h4 v-if="!order.pickup">Delivery Instructions</h4>
            {{ user_detail.delivery }}
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Notes</h4>
            <textarea
              type="text"
              id="form7"
              class="md-textarea form-control"
              rows="3"
              v-model="deliveryNote"
              placeholder="E.G. Customer didn't answer phone or doorbell."
            ></textarea>
            <button
              class="btn btn-primary btn-md pull-right mt-2"
              @click="saveNotes(orderId)"
            >
              Save
            </button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Items</h4>
            <hr />
            <ul class="meal-quantities">
              <li v-for="meal in getMealQuantities(order)" :key="meal.id">
                <div class="row">
                  <div class="col-md-5 pr-0">
                    <span class="order-quantity">{{ meal.quantity }}</span>
                    <img src="/images/store/x-modal.png" class="mr-2 ml-2" />
                    <thumbnail
                      v-if="meal.image.url_thumb"
                      :src="meal.image.url_thumb"
                      :spinner="false"
                      class="mr-0 pr-0"
                    ></thumbnail>
                  </div>
                  <div class="col-md-7 pt-3 nopadding pl-0 ml-0">
                    <p v-html="meal.title"></p>
                    <p class="strong">
                      {{ format.money(meal.subtotal, order.currency) }}
                    </p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Extras</h4>
            <hr />
            <ul class="meal-quantities">
              <li
                v-for="lineItemOrder in order.line_items_orders"
                :key="order.line_items_orders.id"
              >
                <div class="row">
                  <div class="col-md-3">
                    <span class="order-quantity">{{
                      lineItemOrder.quantity
                    }}</span>
                    <img src="/images/store/x-modal.png" class="mr-1 ml-1" />
                  </div>
                  <div class="col-md-9">
                    <p class="mt-1">{{ lineItemOrder.title }}</p>
                    <p class="strong">
                      {{
                        format.money(
                          lineItemOrder.price * lineItemOrder.quantity,
                          order.currency
                        )
                      }}
                    </p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </b-modal>
    </div>
  </div>
</template>

<script>
import Spinner from "../../components/Spinner";
import format from "../../lib/format";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";
import checkDateRange from "../../mixins/deliveryDates";

export default {
  components: {
    Spinner,
    vSelect
  },
  mixins: [checkDateRange],
  data() {
    return {
      ordersByDate: {},
      email: "",
      deliveryDate: "All",
      filter: false,
      pastOrder: false,
      filters: {
        fulfilled: 0,
        paid: 1,
        delivery_dates: {
          start: null,
          end: null
        },
        has_notes: false
      },
      viewOrderModal: false,
      order: {},
      orderId: "",
      user_detail: {},
      meals: {},
      columns: [
        "notes",
        "order_number",
        "user.user_detail.full_name",
        "user.user_detail.address",
        "user.user_detail.zip",
        // "user.user_detail.phone",
        "created_at",
        "delivery_date",
        "pickup",
        "amount",
        "actions"
      ],
      options: {
        headings: {
          notes: "Notes",
          dailyOrderNumber: "Order #",
          order_number: "Order ID",
          "user.user_detail.full_name": "Name",
          "user.user_detail.address": "Address",
          "user.user_detail.zip": "Zip Code",
          // "user.user_detail.phone": "Phone",
          created_at: "Order Placed",
          delivery_date: "Delivery Date",
          pickup: "Type",
          amount: "Total",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `order-${row.id}`;
          classes += row.viewed ? "" : " strong";
          return classes;
        },
        customSorting: {
          created_at: function(ascending) {
            return function(a, b) {
              a = a.created_at;
              b = b.created_at;

              if (ascending) return a.isBefore(b, "day") ? 1 : -1;
              return a.isAfter(b, "day") ? 1 : -1;
            };
          },
          delivery_date: function(ascending) {
            return function(a, b) {
              a = a.delivery_date;
              b = b.delivery_date;

              if (ascending) return a.isBefore(b, "day") ? 1 : -1;
              return a.isAfter(b, "day") ? 1 : -1;
            };
          }
        }
      },
      deliveryNote: ""
    };
  },
  created() {
    this.refreshViewedStore();
  },
  mounted() {
    if (this.storeModules.dailyOrderNumbers) {
      this.columns.splice(1, 0, "dailyOrderNumber");
    }
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      // orders: "storeOrders",
      upcomingOrders: "storeUpcomingOrders",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      nextDeliveryDates: "storeNextDeliveryDates",
      getMeal: "storeMeal",
      storeModules: "storeModules"
    }),
    tableData() {
      let filters = { ...this.filters };

      let orders = {};
      if (this.filters.delivery_dates.start === null) {
        orders = this.upcomingOrders;
      } else {
        orders = this.ordersByDate;
      }
      return orders;
    }
  },
  beforeDestroy() {
    this.updateViewedOrders();
    // this.refreshOrders();
  },
  methods: {
    ...mapActions({
      refreshOrders: "refreshOrders",
      refreshOrdersWithFulfilled: "refreshOrdersWithFulfilled",
      refreshUpcomingOrders: "refreshUpcomingOrders",
      updateOrder: "updateOrder",
      addJob: "addJob",
      removeJob: "removeJob",
      refreshViewedStore: "refreshViewedStore"
    }),
    refreshTable() {
      this.refreshOrders();
    },
    refreshTableWithFulfilled() {
      this.refreshOrdersWithFulfilled();
    },
    formatMoney: format.money,
    syncEditables() {
      this.editing = _.keyBy({ ...this.tableData }, "id");
    },
    getTableDataIndexById(id) {
      return _.findIndex(this.tableData, ["id", id]);
    },
    getTableDataById(id) {
      return _.find(this.tableData, ["id", id]);
    },
    async fulfill(id) {
      await this.updateOrder({ id, data: { fulfilled: 1 } });
      this.$toastr.s("Order fulfilled!");
      this.$forceUpdate();
    },
    async unfulfill(id) {
      await this.updateOrder({ id, data: { fulfilled: 0 } });
      this.$forceUpdate();
    },
    async saveNotes(id) {
      let data = { notes: this.deliveryNote };
      axios.patch(`/api/me/orders/${id}`, data).then(resp => {
        this.refreshTable();
        this.$toastr.s("Order notes saved!");
      });
    },
    getMealQuantities(order) {
      if (!this.initialized || !order.items) return [];

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

        return {
          image: meal.image,
          title: title,
          quantity: item.quantity,
          unit_price: format.money(item.unit_price, order.currency),
          subtotal: format.money(item.price, order.currency)
        };
      });

      return _.filter(data);
    },
    async viewOrder(id) {
      const jobId = await this.addJob();
      axios
        .get(`/api/me/orders/${id}`)
        .then(response => {
          this.orderId = response.data.id;
          this.deliveryNote = response.data.notes;
          this.order = response.data;
          this.user_detail = response.data.user.user_detail;
          this.meals = response.data.meals;
          this.viewOrderModal = true;
          this.email = response.data.user.email;

          this.$nextTick(function() {
            window.dispatchEvent(new window.Event("resize"));
          });
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    },
    filterPastOrders() {
      this.pastOrder = !this.pastOrder;
      this.refreshTable();
    },
    filterNotes() {
      this.filter = !this.filter;
      this.refreshTable();
    },
    async exportData(report, format = "pdf", print = false) {
      const warning = this.checkDateRange({ ...this.filters.delivery_dates });
      if (warning) {
        try {
          let dialog = await this.$dialog.confirm(
            "You have selected a date range which includes delivery days which haven't passe" +
              "d their cutoff period. This means new orders can still come in for those days. Continue?"
          );
          dialog.close();
        } catch (e) {
          return;
        }
      }

      let params = {
        has_notes: this.filters.has_notes ? 1 : 0,
        fulfilled: this.filters.fulfilled ? 1 : 0
      };

      if (
        this.filters.delivery_dates.start &&
        this.filters.delivery_dates.end
      ) {
        params.delivery_dates = {
          from: this.filters.delivery_dates.start,
          to: this.filters.delivery_dates.end
        };
      }

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
    },
    onChangeDateFilter() {
      axios
        .post("/api/me/getOrdersWithDates", {
          start: this.filters.delivery_dates.start,
          end: this.filters.delivery_dates.end
        })
        .then(response => {
          this.ordersByDate = response.data;
        });
    },
    updateViewedOrders() {
      axios.get(`/api/me/ordersUpdateViewed`);
    },
    clearDeliveryDates() {
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.$refs.deliveryDates.clearDates();
    },
    showFulfilledOrders() {
      this.filters.fulfilled = 1;
      this.refreshTableWithFulfilled();
    },
    showUnfulfilledOrders() {
      this.filters.fulfilled = 0;
      this.refreshTable();
    },
    chargeBalance(id) {
      axios
        .post("/api/me/chargeBalance", {
          id: id
        })
        .then(response => {
          this.$toastr.s("Balance successfully charged.");
          this.refreshUpcomingOrders();
        });
    }
  }
};
</script>
