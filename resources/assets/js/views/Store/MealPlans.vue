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
            <span slot="beforeLimit">
              <b-btn variant="primary" @click="exportData('subscriptions', 'pdf', true)">
                <i class="fa fa-print"></i>&nbsp;
                Print
              </b-btn>
              <b-dropdown class="mx-1" right text="Export as">
                <b-dropdown-item @click="exportData('subscriptions', 'csv')">CSV</b-dropdown-item>
                <b-dropdown-item @click="exportData('subscriptions', 'xls')">XLS</b-dropdown-item>
                <b-dropdown-item @click="exportData('subscriptions', 'pdf')">PDF</b-dropdown-item>
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
              slot="delivery_day"
              class="text-nowrap"
              slot-scope="props"
            >{{ moment().day(props.row.delivery_day - 1).format('dddd, MMM Do') }}</div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button
                class="btn view btn-primary btn-sm"
                @click="viewSubscription(props.row.id)"
              >View Meal Plan</button>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount) }}</div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>

    <div class="modal-basic">
      <b-modal v-model="viewSubscriptionModal" size="lg" title="Meal Plan Details">
        <div class="row mt-4">
          <div class="col-md-4">
            <h4>Meal Plan #</h4>
            <p>{{ subscription.stripe_id }}</p>
          </div>
          <div class="col-md-4">
            <h4>Placed On</h4>
            <p>{{ moment(subscription.created_at).format('dddd, MMM Do') }}</p>
          </div>
          <div class="col-md-4">
            <h2>{{ format.money(subscription.amount) }}</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <hr>
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
            <p><strong>Delivery Day:</strong> {{ moment(subscription.delivery_date).format('dddd, MMM Do') }}</p>
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
            <button
              class="btn btn-primary btn-md pull-right mt-2"
              @click="saveNotes(subscriptionId)"
            >Save</button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Meals</h4>
            <hr>
            <ul class="meal-quantities">
              <li v-for="meal in subscription.meals" :key="meal.id">
                <span class="subscription-quantity">{{meal.pivot.quantity}}</span>
                <img src="/images/store/x-modal.png">
                <img :src="meal.featured_image" class="modalMeal">
                {{meal.title}}
                {{ format.money(meal.price * meal.pivot.quantity) }}
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
      filters: {
        delivery_days: ["All"],
        notes: false
      },
      viewSubscriptionModal: false,
      subscription: {},
      subscriptionId: "",
      user_detail: {},
      meals: {},
      columns: [
        // "notes",
        "stripe_id",
        "user.user_detail.full_name",
        "user.user_detail.address",
        "user.user_detail.zip",
        "user.user_detail.phone",
        "amount",
        "created_at",
        "delivery_day",
        // "interval",
        "actions"
      ],
      options: {
        headings: {
          // notes: "Notes",
          stripe_id: "Meal Plan #",
          "user.user_detail.full_name": "Name",
          "user.user_detail.address": "Address",
          "user.user_detail.zip": "Zip Code",
          "user.user_detail.phone": "Phone",
          amount: "Total",
          created_at: "Subscription Placed",
          delivery_day: "Delivery Day",
          // interval: "Interval",
          actions: "Actions"
        },
        rowClassCallback: function(row) {
          let classes = `subscription-${row.id}`;
          return classes;
        },
        customSorting: {
          created_at: function(ascending) {
            return function(a, b) {
              var numA = moment(a.created_at);
              var numB = moment(b.created_at);
              if (ascending) return numA.isBefore(numB, "day") ? 1 : -1;
              return numA.isAfter(numB, "day") ? 1 : -1;
            };
          },
          delivery_day: function(ascending) {
            return function(a, b) {
              var numA = moment(a.delivery_day);
              var numB = moment(b.delivery_day);
              if (ascending) return numA.isBefore(numB, "day") ? 1 : -1;
              return numA.isAfter(numB, "day") ? 1 : -1;
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
      subscriptions: "storeSubscriptions",
      isLoading: "isLoading"
    }),
    tableData() {
      let filters = {};
      if (_.isArray(this.filters.delivery_days)) {
        filters.delivery_days = this.filters.delivery_days;
      }

      if (this.filter) {
        filters.has_notes = true;
      }
      // }
      // if (!this.filter) return _.filter(this.subscriptions, { fulfilled: 0 });
      //   else return _.filter(this.subscriptions, { fulfilled: 0, has_notes: true });
      const subs = _.filter(this.subscriptions, subscription => {
        if ("delivery_days" in filters) {
          let dateMatch = _.reduce(
            filters.delivery_days,
            (match, date) => {
              if (date === "All") {
                return true;
              }
              if (moment(date).isSame(subscription.delivery_day, "day")) {
                return true;
              }

              return match;
            },
            false
          );

          if (!dateMatch) return false;
        }

        if ("status" in filters && subscription.status !== filters.status)
          return false;

        return true;
      });

      return subs;
    },
    deliveryDays() {
      let grouped = [];
      this.subscriptions.forEach(subscription => {
        if (!_.includes(grouped, subscription.delivery_day)) {
          grouped.push(subscription.delivery_day);
        }
      });
      grouped.push("All");
      this.deliveryDate = grouped[0];
      return grouped;
    },
    selected() {
      return this.deliveryDays;
    }
  },
  methods: {
    ...mapActions({
      refreshSubscriptions: "refreshStoreSubscriptions",
      updateSubscription: "updateSubscription"
    }),
    refreshTable() {
      this.refreshSubscriptions();
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
    async saveNotes(id) {
      let deliveryNote = deliveryNote;
      await this.updateSubscription({ id, data: { notes: this.deliveryNote } });
    },
    getMealQuantities(meals) {
      let subscription = _.toArray(_.countBy(meals, "id"));

      return subscription.map((subscription, id) => {
        return {
          subscription,
          featured_image: meals[id].featured_image,
          title: meals[id].title,
          price: meals[id].price
        };
      });
    },
    viewSubscription(id) {
      axios.get(`/api/me/subscriptions/${id}`).then(response => {
        this.subscriptionId = response.data.id;
        this.deliveryNote = response.data.notes;
        this.subscription = response.data;
        this.user_detail = response.data.user.user_detail;
        this.meals = response.data.meals;

        this.$nextTick(function() {
          window.dispatchEvent(new window.Event("resize"));
        });
      });
      this.viewSubscriptionModal = true;
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
    }
  }
};
</script>