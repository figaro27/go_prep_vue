<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <b-modal
            v-model="showCancelModal"
            title="Cancel Subscription"
            size="md"
            :hide-footer="true"
            no-fade
          >
            <p class="center-text mb-3 mt-3">
              Are you sure you want to cancel this subscription?
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
            <div slot="interval" class="text-nowrap" slot-scope="props">
              {{ props.row.interval_title }}
            </div>
            <div slot="delivery_day" class="text-nowrap" slot-scope="props">
              {{ moment(props.row.next_delivery_date).format("dddd, MMM Do") }}
            </div>
            <div
              slot="charge_day"
              class="text-nowrap"
              slot-scope="props"
              v-if="storeSettings.timezone"
            >
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
                class="btn btn-danger btn-sm"
                @click="deleteMealPlan(props.row.id)"
              >
                Cancel
              </button>
              <!--Removing pause functionality for the time being -->

              <!-- <b-btn
                v-if="props.row.status === 'active'"
                class="btn btn-warning btn-sm"
                @click.stop="() => pauseSubscription(props.row.id)"
                >Pause</b-btn
              >
              <b-btn
                v-if="props.row.status === 'paused'"
                class="btn btn-warning btn-sm"
                @click.stop="() => resumeSubscription(props.row.id)"
                >Resume</b-btn
              > -->
              <router-link :to="`/store/adjust-meal-plan/${props.row.id}`">
                <b-btn class="btn btn-success btn-sm">Edit</b-btn>
              </router-link>
            </div>

            <div slot="amount" slot-scope="props">
              <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
            </div>
          </v-client-table>
        </div>
      </div>
    </div>

    <div v-if="subscription" class="modal-basic">
      <b-modal
        v-model="viewSubscriptionModal"
        size="lg"
        title="Subscription Details"
        no-fade
      >
        <div class="row mt-4">
          <div class="col-md-4">
            <h4>Subscription ID</h4>
            <p>{{ subscription.stripe_id }}</p>
          </div>
          <div class="col-md-4">
            <h4>Placed On</h4>
            <p>{{ moment(subscription.created_at).format("dddd, MMM Do") }}</p>
          </div>
          <div class="col-md-4">
            <p>
              Subtotal:
              {{
                format.money(
                  subscription.preFeePreDiscount,
                  subscription.currency
                )
              }}
            </p>
            <p class="text-success" v-if="subscription.couponReduction > 0">
              Coupon {{ subscription.couponCode }}: ({{
                format.money(
                  subscription.couponReduction,
                  subscription.currency
                )
              }})
            </p>
            <p v-if="subscription.mealPlanDiscount > 0" class="text-success">
              Subscription Discount: ({{
                format.money(
                  subscription.mealPlanDiscount,
                  subscription.currency
                )
              }})
            </p>
            <p v-if="subscription.salesTax > 0">
              Sales Tax:
              {{ format.money(subscription.salesTax, subscription.currency) }}
            </p>
            <p v-if="subscription.deliveryFee > 0">
              Delivery Fee:
              {{
                format.money(subscription.deliveryFee, subscription.currency)
              }}
            </p>
            <p v-if="subscription.processingFee > 0">
              Processing Fee:
              {{
                format.money(subscription.processingFee, subscription.currency)
              }}
            </p>

            <p class="strong">
              Total:
              {{ format.money(subscription.amount, subscription.currency) }}
            </p>
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
            <span v-if="!storeModules.hideTransferOptions">
              <h4 v-if="!subscription.pickup">Delivery Day</h4>
              <h4 v-if="subscription.pickup">Pickup Day</h4>
              {{ moment(subscription.delivery_date).format("dddd, MMM Do") }}
              <span v-if="subscription.transferTime">
                {{ subscription.transferTime }}</span
              >
            </span>
            <p v-if="subscription.pickup_location_id != null" class="mt-1">
              <b>Pickup Location:</b>
              {{ subscription.pickup_location.name }},
              {{ subscription.pickup_location.address }},
              {{ subscription.pickup_location.city }},
              {{ subscription.pickup_location.state }}
              {{ subscription.pickup_location.zip }}<br />
              <span v-if="subscription.pickup_location.instructions">
                <b>Instructions:</b>
                {{ subscription.pickup_location.instructions }}
              </span>
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
            <h4 v-if="!subscription.pickup">Delivery Instructions</h4>
            <p>{{ user_detail.delivery }}</p>
          </div>
        </div>
        <div class="row" v-if="storeModules.orderNotes">
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
            <h4>Items</h4>
            <hr />
            <v-client-table
              striped
              stacked="sm"
              :columns="columnsMeal"
              :data="getMealTableData(subscription)"
              foot-clone
            >
              <template slot="meal" slot-scope="props">
                <div v-html="props.row.meal"></div>
              </template>

              <template slot="FOOT_subtotal" slot-scope="props">
                <p>
                  Subtotal:
                  {{
                    format.money(
                      subscription.preFeePreDiscount,
                      subscription.currency
                    )
                  }}
                </p>
                <p class="text-success" v-if="subscription.couponReduction > 0">
                  Coupon {{ subscription.couponCode }}: ({{
                    format.money(
                      subscription.couponReduction,
                      subscription.currency
                    )
                  }})
                </p>
                <p
                  v-if="subscription.mealPlanDiscount > 0"
                  class="text-success"
                >
                  Subscription Discount: ({{
                    format.money(
                      subscription.mealPlanDiscount,
                      subscription.currency
                    )
                  }})
                </p>
                <p v-if="subscription.deliveryFee > 0">
                  Delivery Fee:
                  {{
                    format.money(
                      subscription.deliveryFee,
                      subscription.currency
                    )
                  }}
                </p>
                <p v-if="subscription.processingFee > 0">
                  Processing Fee:
                  {{
                    format.money(
                      subscription.processingFee,
                      subscription.currency
                    )
                  }}
                </p>
                <p v-if="subscription.salesTax > 0">
                  Sales Tax:
                  {{
                    format.money(subscription.salesTax, subscription.currency)
                  }}
                </p>
                <p class="strong">
                  Total:
                  {{ format.money(subscription.amount, subscription.currency) }}
                </p>
              </template>

              <template slot="table-caption"></template>
            </v-client-table>
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
import { sidebarCssClasses } from "../../shared/classes";
import store from "../../store";

export default {
  components: {
    Spinner,
    vSelect
  },

  data() {
    return {
      email: "",
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
      columnsMeal: ["size", "meal", "unit_price", "subtotal"],
      columns: [
        // "notes",
        "stripe_id",
        "interval",
        "user.user_detail.full_name",
        "user.user_detail.address",
        "user.user_detail.zip",
        "user.user_detail.phone",
        "amount",
        "created_at",
        "delivery_day",
        "charge_day",
        // "interval",
        "status",
        "actions"
      ],
      options: {
        headings: {
          // notes: "Notes",
          stripe_id: "Subscription #",
          interval: "Interval",
          "user.user_detail.full_name": "Name",
          "user.user_detail.address": "Address",
          "user.user_detail.zip": "Zip Code",
          "user.user_detail.phone": "Phone",
          amount: "Total",
          created_at: "Subscription Placed",
          delivery_day: "Delivery Day",
          charge_day: "Charge Day",
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
  mounted() {
    if (!this.isLazyStore) {
      store.dispatch("refreshLazyStore");
    }

    if (this.$route.query.updated) {
      this.refreshSubscriptions();
      this.$toastr.s("Subscription Updated");
    }

    /* Sidebar Check */
    let isOpen = false;

    for (let i in sidebarCssClasses) {
      if ($("body").hasClass(sidebarCssClasses[i])) {
        isOpen = true;
        break;
      }
    }

    if (!isOpen && $(".navbar-toggler").length > 0) {
      $(".navbar-toggler").click();
    }
    /* Sidebar Check End */
  },
  computed: {
    ...mapGetters({
      store: "viewedStore",
      storeSettings: "storeSettings",
      subscriptions: "storeSubscriptions",
      isLoading: "isLoading",
      initialized: "initialized",
      getMeal: "storeMeal",
      storeModules: "storeModules",
      isLazyStore: "isLazyStore",
      getStoreMeal: "storeMeal"
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
    ...mapMutations([
      "emptyBag",
      "addBagItems",
      "setBagMealPlan",
      "setBagCoupon"
    ]),
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
    getMealQuantities(subscription) {
      if (!this.initialized || !subscription.items) return [];

      let data = subscription.items.map(item => {
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
          unit_price: format.money(item.unit_price, subscription.currency),
          subtotal: format.money(item.price, subscription.currency)
        };
      });

      return _.filter(data);
    },
    viewSubscription(id) {
      axios.get(`/api/me/subscriptions/${id}`).then(response => {
        this.subscriptionId = response.data.id;
        this.deliveryNote = response.data.notes;
        this.subscription = response.data;
        this.user_detail = response.data.user.user_detail;
        this.meals = response.data.meals;
        this.delivery_day = response.data.delivery_day;
        this.email = response.data.user.email;

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
        this.$toastr.s("Subscription Cancelled");
      });
    },
    pauseSubscription(id) {
      try {
        axios.post("/api/me/subscriptions/pause", { id: id }).then(resp => {
          this.refreshSubscriptions();
          this.$toastr.s("Subscription paused.");
        });
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to pause Subscription"
        );
      }
    },
    resumeSubscription(id) {
      try {
        axios.post("/api/me/subscriptions/resume", { id: id }).then(resp => {
          this.refreshSubscriptions();
          this.$toastr.s("Subscription resumed.");
        });
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to resume Subscription"
        );
      }
    },
    getMealTableData(subscription) {
      if (!this.initialized || !subscription.items) return [];

      let data = [];

      subscription.meal_package_items.forEach(meal_package_item => {
        if (meal_package_item.meal_package_size === null) {
          data.push({
            size: meal_package_item.meal_package.default_size_title,
            meal: meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(
              meal_package_item.price,
              subscription.currency
            ),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              subscription.currency
            )
          });
        } else {
          data.push({
            size: meal_package_item.meal_package_size.title,
            meal: meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(
              meal_package_item.price,
              subscription.currency
            ),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              subscription.currency
            )
          });
        }

        subscription.items.forEach(item => {
          if (item.meal_package_subscription_id === meal_package_item.id) {
            const meal = this.getStoreMeal(item.meal_id);
            if (!meal) {
              return null;
            }
            const size = meal.getSize(item.meal_size_id);
            const title = meal.getTitle(
              true,
              size,
              item.components,
              item.addons,
              item.special_instructions,
              false
            );

            data.push({
              size: size ? size.title : meal.default_size_title,
              //meal: meal.title,
              meal: title,
              quantity: item.quantity,
              unit_price: "In Package",
              subtotal: "In Package"
            });
          }
        });
      });

      subscription.items.forEach(item => {
        if (item.meal_package_subscription_id === null) {
          const meal = this.getStoreMeal(item.meal_id);
          if (!meal) {
            return null;
          }
          const size = meal.getSize(item.meal_size_id);
          const title = meal.getTitle(
            true,
            size,
            item.components,
            item.addons,
            item.special_instructions,
            false
          );

          data.push({
            size: size ? size.title : meal.default_size_title,
            //meal: meal.title,
            meal: title,
            unit_price:
              item.attached || item.free
                ? "Included"
                : format.money(item.unit_price, subscription.currency),
            subtotal:
              item.attached || item.free
                ? "Included"
                : format.money(item.price, subscription.currency)
          });
        }
      });

      return _.filter(data);
    }
  }
};
</script>
