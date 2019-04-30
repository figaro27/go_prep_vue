<template>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <b-modal
            v-model="showCancelModal"
            title="Cancel Meal Plan"
            size="md"
            :hide-footer="true"
          >
            <p class="center-text mb-3 mt-3">
              Are you sure you want to cancel this meal plan?
            </p>
            <b-btn variant="danger" class="center" @click="destroyMealPlan"
              >Cancel</b-btn
            >
          </b-modal>

          <Spinner v-if="isLoading" />
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
            v-show="!isLoading"
          >
            <span slot="beforeLimit">
              <b-btn
                variant="primary"
                @click="exportData('subscriptions', 'pdf', true)"
              >
                <i class="fa fa-print"></i>&nbsp; Print
              </b-btn>
              <b-dropdown class="mx-1" right text="Export as">
                <b-dropdown-item @click="exportData('subscriptions', 'csv')"
                  >CSV</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('subscriptions', 'xls')"
                  >XLS</b-dropdown-item
                >
                <b-dropdown-item @click="exportData('subscriptions', 'pdf')"
                  >PDF</b-dropdown-item
                >
              </b-dropdown>
            </span>

            <div slot="delivery_day" class="text-nowrap" slot-scope="props">
              {{ moment(props.row.next_delivery_date).format("dddd, MMM Do") }}
            </div>
            <div
              slot="charge_time"
              class="text-nowrap"
              slot-scope="props"
              v-if="storeSettings.timezone"
            >
              <!-- {{
                momentTimezone
                  .tz(props.row.charge_time, storeSettings.timezone)
                  .format("dddd")
              }} -->
              {{ moment(props.row.next_renewal_at).format("dddd") }}
            </div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <button
                class="btn view btn-primary btn-sm"
                @click="viewSubscription(props.row.id)"
              >
                View
              </button>
              <button
                class="btn view btn-danger btn-sm"
                @click="deleteMealPlan(props.row.id)"
              >
                Cancel Meal Plan
              </button>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount) }}</div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>

    <div v-if="subscription" class="modal-basic">
      <b-modal
        v-model="viewSubscriptionModal"
        size="lg"
        title="Meal Plan Details"
      >
        <div class="row mt-4">
          <div class="col-md-4">
            <h4>Meal Plan #</h4>
            <p>{{ subscription.stripe_id }}</p>
          </div>
          <div class="col-md-4">
            <h4>Placed On</h4>
            <p>{{ moment(subscription.created_at).format("dddd, MMM Do") }}</p>
          </div>
          <div class="col-md-4">
            <span>
              Subtotal:
              {{ format.money(subscription.preFeePreDiscount) }} </span
            ><br />
            <span v-if="subscription.mealPlanDiscount > 0">
              Meal Plan Discount:
              <span class="text-success"
                >({{ format.money(subscription.mealPlanDiscount) }})
              </span>
              <br />
            </span>
            <span v-if="subscription.deliveryFee > 0">
              Delivery Fee: {{ format.money(subscription.deliveryFee) }}
              <br />
            </span>
            <span v-if="subscription.processingFee > 0">
              Processing Fee:
              {{ format.money(subscription.processingFee) }}
              <br />
            </span>
            <span>Sales Tax: {{ format.money(subscription.salesTax) }}</span
            ><br />
            <span>
              <strong>Total: {{ format.money(subscription.amount) }}</strong>
            </span>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <hr />
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
            <p>
              {{ user_detail.address }}<br />
              {{ user_detail.city }}, {{ user_detail.state }}<br />
              {{ user_detail.zip }}
            </p>
          </div>
          <div class="col-md-4">
            <h4 v-if="!subscription.pickup">Delivery Day</h4>
            <h4 v-if="subscription.pickup">Pickup Day</h4>
            {{ moment(subscription.delivery_date).format("dddd, MMM Do") }}
            <p v-if="!subscription.pickup" class="mt-3">
              <strong>Delivery Instructions</strong>
              {{ user_detail.delivery }}
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
            <button
              class="btn btn-primary btn-md pull-right mt-2"
              @click="saveNotes(subscriptionId)"
            >
              Save
            </button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h4>Meals</h4>
            <hr />
            <ul class="meal-quantities">
              <li v-for="meal in getMealData(subscription)" :key="meal.id">
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
                    <p>{{ meal.title }}</p>
                    <p class="strong">
                      {{ format.money(meal.price * meal.quantity) }}
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

export default {
  components: {
    Spinner,
    vSelect
  },

  data() {
    return {
      showCancelModal: false,
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
        "charge_time",
        // "interval",
        "status",
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
          created_at: "Meal Plan Placed",
          delivery_day: "Delivery Day",
          charge_time: "Charge Day",
          // interval: "Interval",
          status: "Status",
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
        orderBy: {
          column: "created_at"
        }
      },
      deliveryNote: ""
    };
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeSettings: "storeSettings",
      subscriptions: "storeSubscriptions",
      isLoading: "isLoading",
      getMeal: "storeMeal"
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

      const activeSubs = _.filter(subs, sub => {
        if (sub.status != "cancelled") {
          return true;
        }
      });

      return activeSubs;
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
          featured_image: meals[id].image.url_thumb,
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
        this.delivery_day = response.data.delivery_day;

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
    },
    getMealData(subscription) {
      if (!subscription || !subscription.meal_ids) return [];
      return subscription.meal_ids.map(id => {
        return {
          ...this.getMeal(id),
          quantity: subscription.meal_quantities[id]
        };
      });
    },
    deleteMealPlan(id) {
      this.subscriptionId = id;
      this.showCancelModal = true;
    },
    destroyMealPlan() {
      let id = this.subscriptionId;
      axios.delete(`/api/me/subscriptions/${id}`).then(resp => {
        this.refreshTable();
        this.showCancelModal = false;
        this.$toastr.s("Meal Plan Cancelled");
      });
    }
  }
};
</script>
