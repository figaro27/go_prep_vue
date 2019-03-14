<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <Spinner v-if="isLoading"/>
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
            v-show="initialized"
          >
            <div slot="beforeTable" class="mb-2">
              <div class="table-before d-flex flex-wrap align-items-center">
                <div class="d-inline mr-2 flex-grow-0">
                  <b-btn
                    @click="$set(filters, 'has_notes', !filters.has_notes)"
                    :selected="filters.has_notes"
                    variant="primary"
                    class="filter-btn"
                  >Filter Delivery Notes</b-btn>
                </div>
                <div class="d-inline mr-2 flex-grow-0">
                  <b-btn
                    @click="$set(filters, 'fulfilled', !filters.fulfilled)"
                    :selected="filters.fulfilled"
                    variant="warning"
                    class="filter-btn"
                    v-if="!filters.fulfilled"
                  >View Completed Orders</b-btn>
                  <b-btn
                    @click="$set(filters, 'fulfilled', !filters.fulfilled)"
                    :selected="filters.fulfilled"
                    variant="danger"
                    class="filter-btn"
                    v-if="filters.fulfilled"
                  >View Open Orders</b-btn>
                  <!-- <router-link to="/store/menu/manual-order">
                    <b-btn class="btn btn-success filter-btn">Create Manual Order</b-btn>
                  </router-link> -->
                </div>
                <delivery-date-picker v-model="filters.delivery_dates" @change="onChangeDateFilter" class="mt-3 mt-sm-0"></delivery-date-picker>
                <b-btn @click="clearDeliveryDates" class="ml-1">Clear</b-btn>
              </div>
            </div>

            <span slot="beforeLimit">
              <b-btn variant="success" @click="exportData('orders_by_customer', 'pdf', true)">
                <i class="fa fa-print"></i>&nbsp;
                Print Orders Summary
              </b-btn>
              <b-btn variant="primary" @click="exportData('orders', 'pdf', true)">
                <i class="fa fa-print"></i>&nbsp;
                Print Orders
              </b-btn>
              <b-dropdown class="mx-1 mt-2 mt-sm-0" right text="Export as">
                <b-dropdown-item @click="exportData('orders', 'csv')">CSV</b-dropdown-item>
                <b-dropdown-item @click="exportData('orders', 'xls')">XLS</b-dropdown-item>
                <b-dropdown-item @click="exportData('orders', 'pdf')">PDF</b-dropdown-item>
              </b-dropdown>
            </span>

            <div slot="notes" class="text-nowrap" slot-scope="props">
              <p v-if="props.row.has_notes">
                <img src="/images/store/note.png">
              </p>
            </div>
            <div
              slot="created_at"
              slot-scope="props"
            >{{ moment(props.row.created_at).format('dddd, MMM Do') }}</div>
            <div
              slot="delivery_date"
              slot-scope="props"
            >{{ moment(props.row.delivery_date).format('dddd, MMM Do') }}</div>
            <div slot="pickup" slot-scope="props">{{ props.row.pickup ? 'Pickup' : 'Delivery' }}</div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button
                class="btn view btn-warning btn-sm"
                @click="viewOrder(props.row.id)"
              >View Order</button>
              <b-btn
                v-if="!props.row.fulfilled"
                class="btn btn-primary btn-sm"
                @click="fulfill(props.row.id)"
                variant="primary"
              >Mark As Complete</b-btn>
              <b-btn
                v-else
                class="btn btn-primary btn-sm"
                @click="unfulfill(props.row.id)"
                variant="danger"
              >Unmark As Complete</b-btn>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount) }}</div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>

    <div class="modal-basic modal-wider">
      <b-modal v-model="viewOrderModal" size="lg" title="Order Information">
        <div class="row light-background border-bottom mb-3">
          <div class="col-md-4 pt-4">
            <h4>Order ID</h4>
            <p>{{ order.order_number }}</p>
          </div>
          <div class="col-md-4 pt-4">
            <h4>Placed On</h4>
            <p>{{ moment(order.created_at).format('dddd, MMM Do') }}</p>
          </div>
          <div class="col-md-4 pt-4">
            <h2>{{ format.money(order.amount) }}</h2>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <h4>Customer</h4>
            <p>{{ user_detail.firstname }} {{ user_detail.lastname }}</p>

            <h4>Phone</h4>
            <p>{{ user_detail.phone }}</p>
          </div>
          <div class="col-md-4">
            <h4>Address</h4>
            <p>{{ user_detail.address }}</p>
            <p>{{ user_detail.city }}, {{ user_detail.state }}</p>
            <p>{{ user_detail.zip }}</p>
          </div>
          <div v-if="!order.pickup" class="col-md-4">
            <h4>Delivery Instructions</h4>
            <p>{{ user_detail.delivery }}</p>
            <p>
              <strong>Delivery Day:</strong>
              {{ moment(order.delivery_date).format('dddd, MMM Do') }}
            </p>
          </div>
          <div v-else class="col-md-4">
            <h4>Pickup</h4>
            <p>
              <strong>Day:</strong>
              {{ moment(order.delivery_date).format('dddd, MMM Do') }}
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Delivery Notes</h4>
            <textarea
              type="text"
              id="form7"
              class="md-textarea form-control"
              rows="3"
              v-model="deliveryNote"
              placeholder="E.G. Customer didn't answer phone or doorbell."
            ></textarea>
            <button class="btn btn-primary btn-md pull-right mt-2" @click="saveNotes(orderId)">Save</button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Meals</h4>
            <hr>
            <ul class="meal-quantities">
              <li v-for="(meal) in getMealQuantities(order.meals)">
                <div class="row">
                  <div class="col-md-5 pr-0">
                    <span class="order-quantity">{{meal.quantity}}</span>
                    <img src="/images/store/x-modal.png" class="mr-2 ml-2">
                    <img :src="meal.featured_image" class="modalMeal mr-0 pr-0">
                  </div>
                  <div class="col-md-7 pt-3 nopadding pl-0 ml-0">
                    <p>{{meal.title}}</p>
                    <p class="strong">{{format.money(meal.price * meal.quantity)}}</p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </b-modal>
    </div>
      <v-style>
        .input-date{
          color: {{ dateColor }}
        }
      </v-style>
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
      dateColor: "",
      deliveryDate: "All",
      filter: false,
      pastOrder: false,
      filters: {
        fulfilled: 0,
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
          order_number: "Order #",
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
        },
      },
      deliveryNote: ""
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      isLoading: "isLoading",
      initialized: "initialized",
      customers: "storeCustomers",
      nextDeliveryDates: "storeNextDeliveryDates"
    }),
    tableData() {
      let filters = { ...this.filters };

      let filtered = _.filter(this.orders, order => {
        if (
          "delivery_dates" in filters &&
          (filters.delivery_dates.start ||
          filters.delivery_dates.end)
        ) {
          let dateMatch = false;

          if (filters.delivery_dates.start && filters.delivery_dates.end) {
            dateMatch = order.delivery_date
              .hours(12)
              .isBetween(
                filters.delivery_dates.start,
                filters.delivery_dates.end,
                "date",
                "[]"
              );
          } else if (filters.delivery_dates.start) {
            dateMatch = order.delivery_date
              .hours(12)
              .isSameOrAfter(filters.delivery_dates.start, "date", "[]");
          } else if (filters.delivery_dates.end) {
            dateMatch = order.delivery_date
              .hours(12)
              .isSameOrBefore(filters.delivery_dates.end, "date", "[]");
          }

          if (!dateMatch) return false;
        }

        if (filters.has_notes && !order.has_notes) return false;
        if (order.fulfilled != filters.fulfilled) return false;

        return true;
      });

      return filtered.map(order => {
        order.customer = _.find(this.customers, { user_id: order.user_id });
        return order;
      });
    }
  },
  beforeDestroy() {
    this.updateViewedOrders();
    this.refreshOrders();
  },
  methods: {
    ...mapActions({
      refreshOrders: "refreshOrders",
      updateOrder: "updateOrder",
      addJob: "addJob",
      removeJob: "removeJob",
    }),
    refreshTable() {
      this.refreshOrders();
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
      let deliveryNote = deliveryNote;
      await this.updateOrder({ id, data: { notes: this.deliveryNote } });
      this.$toastr.s("Order notes saved!");
      this.$forceUpdate();
    },
    getMealQuantities(meals) {
      if (!_.isArray(meals)) {
        return [];
      }
      return meals.map((meal, id) => {
        return {
          quantity: meal.pivot.quantity,
          featured_image: meals[id].featured_image,
          title: meals[id].title,
          price: meals[id].price
        };
      });
    },
    async viewOrder(id) {
      const jobId = await this.addJob();
      axios.get(`/api/me/orders/${id}`).then(response => {
        this.orderId = response.data.id;
        this.deliveryNote = response.data.notes;
        this.order = response.data;
        this.user_detail = response.data.user.user_detail;
        this.meals = response.data.meals;
        this.viewOrderModal = true;

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
        fulfilled: this.filters.fulfilled ? 1 : 0,
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
      this.dateColor = "#5c6873 !important";
    },
    updateViewedOrders() {
      axios.get(`/api/me/ordersUpdateViewed`);
    },
    clearDeliveryDates(){
      this.filters.delivery_dates.start = null;
      this.filters.delivery_dates.end = null;
      this.dateColor = "#ffffff !important";
    }
  }
};
</script>