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
            v-show="!isLoading"
          >
            <div slot="beforeTable" class="mb-2">
              <div class="d-flex align-items-center">
                <div class="mr-2">
                  <b-btn
                    @click="$set(filters, 'has_notes', !filters.has_notes)"
                    :selected="filters.has_notes"
                    variant="primary"
                    class="filter-btn"
                  >Filter Delivery Notes</b-btn>
                </div>
                <div class="mr-2">
                  <b-btn
                    @click="$set(filters, 'fulfilled', !filters.fulfilled)"
                    :selected="filters.fulfilled"
                    variant="warning"
                    class="filter-btn"
                  >View Completed Orders</b-btn>
                </div>
                <delivery-date-picker v-model="filters.delivery_dates"></delivery-date-picker>
              </div>
            </div>

            <span slot="beforeLimit">
              <b-btn variant="primary" @click="exportData('orders', 'pdf', true)">
                <i class="fa fa-print"></i>&nbsp;
                Print
              </b-btn>
              <b-dropdown class="mx-1" right text="Export as">
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
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button
                class="btn view btn-warning btn-sm"
                @click="viewOrder(props.row.id)"
              >View Order</button>
              <button
                v-if="!props.row.fulfilled"
                class="btn btn-primary btn-sm"
                @click="fulfill(props.row.id)"
              >Mark As Delivered</button>
              <button
                v-else
                class="btn btn-primary btn-sm"
                @click="unfulfill(props.row.id)"
              >Unmark As Delivered</button>
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
            <p>{{ order.created_at }}</p>
          </div>
          <div class="col-md-4 pt-4">
            <h2>${{ order.amount }}</h2>
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
          <div class="col-md-4">
            <h4>Delivery Instructions</h4>
            <p>{{ user_detail.delivery }}</p>
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
  </div>
</template>



<script>
import Spinner from "../../components/Spinner";
import format from "../../lib/format";
import vSelect from "vue-select";
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
  components: {
    Spinner,
    vSelect
  },

  data() {
    return {
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
          amount: "Total",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `order-${row.id}`;
          return classes;
        },
        customSorting: {
          created_at: function(ascending) {
            return function(a, b) {
              if (ascending) return a.isBefore(b, "day") ? 1 : -1;
              return numA.isAfter(b, "day") ? 1 : -1;
            };
          },
          delivery_date: function(ascending) {
            return function(a, b) {
              if (ascending) return a.isBefore(b, "day") ? 1 : -1;
              return numA.isAfter(b, "day") ? 1 : -1;
            };
          }
        }
      },
      deliveryNote: ""
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      orders: "storeOrders",
      isLoading: "isLoading",
      customers: "storeCustomers"
    }),
    tableData() {
      let filters = { ...this.filters };

      let filtered = _.filter(this.orders, order => {
        if (
          "delivery_dates" in filters &&
          filters.delivery_dates.start &&
          filters.delivery_dates.end
        ) {
          let dateMatch = false;

          if (filters.delivery_dates.start && filters.delivery_dates.end) {
            dateMatch = order.delivery_date.isBetween(
              filters.delivery_dates.start,
              filters.delivery_dates.end
            );
          } else if (filters.delivery_dates.start) {
            dateMatch = order.delivery_date.isAfter(
              filters.delivery_dates.start
            );
          } else if (filters.delivery_dates.end) {
            dateMatch = order.delivery_date.isBefore(
              filters.delivery_dates.end
            );
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
  methods: {
    ...mapActions({
      refreshOrders: "refreshOrders",
      updateOrder: "updateOrder"
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
    viewOrder(id) {
      axios.get(`/api/me/orders/${id}`).then(response => {
        this.orderId = response.data.id;
        this.deliveryNote = response.data.notes;
        this.order = response.data;
        this.user_detail = response.data.user.user_detail;
        this.meals = response.data.meals;

        this.$nextTick(function() {
          window.dispatchEvent(new window.Event("resize"));
        });
      });
      this.viewOrderModal = true;
    },
    filterPastOrders() {
      this.pastOrder = !this.pastOrder;
      this.refreshTable();
    },
    filterNotes() {
      this.filter = !this.filter;
      this.refreshTable();
    },
    exportData(report, format = "pdf", print = false) {
      let params = {};

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
    }
  }
};
</script>