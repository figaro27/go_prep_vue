<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <b-btn
            variant="success"
            v-if="store.id === 3"
            @click="testRunHourlyJob"
            >RUN HOURLY JOB</b-btn
          >
          <br /><br />
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
            <b-btn variant="danger" class="center" @click="cancelSubscription"
              >Cancel</b-btn
            >
          </b-modal>

          <b-modal
            v-model="showRenewModal"
            title="Renew Subscription"
            size="md"
            :hide-footer="true"
            no-fade
          >
            <p class="center-text mb-3 mt-3">
              Are you sure you want to renew this subscription? The customer
              will be charged and a new order will be created.
            </p>
            <b-btn variant="primary" class="center" @click="renew()"
              >Renew</b-btn
            >
          </b-modal>

          <Spinner v-if="isLoading" />
          <v-client-table
            :columns="columns"
            :data="tableData"
            :options="options"
          >
            <div slot="beforeTable" class="mb-2">
              <b-form-checkbox
                v-model="showingCancelledSubscriptions"
                @change="val => showCancelledSubscriptions(val)"
              >
                <p>Show Cancelled</p>
              </b-form-checkbox>
            </div>
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
            <div slot="status" class="text-nowrap" slot-scope="props">
              {{
                props.row.status.charAt(0).toUpperCase() +
                  props.row.status.slice(1)
              }}
              <span v-if="props.row.failed_renewal">
                <img
                  v-b-popover.hover="
                    'This subscription failed to renew. This is most likely due to a credit card issue. An email was sent to you and the customer at the time of failure. Once the issue has been sorted out you can manually renew this subscription.'
                  "
                  title="Failed Renewal"
                  src="/images/store/caution.png"
                  class="pb-1"
                />
              </span>
            </div>
            <div slot="interval" class="text-nowrap" slot-scope="props">
              {{ props.row.interval_title }}
            </div>
            <div
              slot="created_at"
              class="text-nowrap"
              slot-scope="props"
              v-if="storeSettings.timezone"
            >
              <span v-if="props.row.created_at">{{
                moment(props.row.created_at).format("dddd, MMM Do")
              }}</span>
            </div>
            <div
              slot="next_renewal"
              class="text-nowrap"
              slot-scope="props"
              v-if="storeSettings.timezone"
            >
              <span v-if="props.row.status === 'active'">{{
                moment(props.row.adjustedRenewal.date).format("dddd, MMM Do")
              }}</span>
            </div>
            <div
              slot="next_delivery_date"
              class="text-nowrap"
              slot-scope="props"
            >
              <span
                v-if="
                  props.row.status !== 'cancelled' &&
                    props.row.next_delivery_date
                "
              >
                {{
                  moment(props.row.next_delivery_date).format("dddd, MMM Do")
                }}
              </span>
            </div>
            <div slot="actions" class="text-nowrap" slot-scope="props">
              <!-- Keeping but hiding for purposes of double clicking the row to open the modal -->
              <button
                v-show="false"
                class="btn view btn-primary btn-sm"
                @click="viewSubscription(props.row.id)"
              >
                View Subscription
              </button>

              <button
                type="button"
                class="btn btn-primary dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                Actions
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" @click="viewSubscription(props.row.id)"
                  >View</a
                >
                <span v-if="!showingCancelledSubscriptions">
                  <a
                    class="dropdown-item"
                    v-if="!mealMixItems.isRunningLazy && !props.row.prepaid"
                    @click="adjust(props.row.id)"
                  >
                    Adjust
                  </a>
                  <a
                    class="dropdown-item"
                    v-if="
                      props.row.status == 'active' && !subscription.cancelled_at
                    "
                    @click="() => pauseSubscription(props.row.id)"
                    >Pause</a
                  >
                  <a
                    class="dropdown-item"
                    v-if="
                      props.row.status == 'paused' && !subscription.cancelled_at
                    "
                    @click="() => resumeSubscription(props.row.id)"
                    >Resume</a
                  >
                  <a class="dropdown-item" @click="renewModal(props.row.id)"
                    >Renew</a
                  >
                  <a
                    class="dropdown-item"
                    @click="showCancellationModal(props.row.id)"
                    v-if="props.row.cancelled_at === null"
                    >Cancel</a
                  >
                </span>
              </div>
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
        <div
          class="row"
          v-if="subscription.store_updated || subscription.customer_updated"
        >
          <div class="col-md-12">
            <b-alert
              show
              variant="warning"
              class="center-text mb-1 pb-1 mt-1 pt-1"
              v-if="
                updatedAfterRenewal(subscription.store_updated) ||
                  updatedAfterRenewal(subscription.customer_updated)
              "
            >
              <div
                v-if="
                  subscription.store_updated &&
                    updatedAfterRenewal(subscription.store_updated)
                "
              >
                You updated this subscription on
                {{ moment(subscription.store_updated).format("dddd, MMM Do") }}.
              </div>
              <div
                v-if="
                  subscription.customer_updated &&
                    updatedAfterRenewal(subscription.customer_updated)
                "
              >
                The customer updated this subscription on
                {{
                  moment(subscription.customer_updated).format("dddd, MMM Do")
                }}.
              </div>
            </b-alert>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-4">
            <h4>Subscription ID</h4>
            <p>{{ subscription.stripe_id }}</p>
            <span v-if="!showingCancelledSubscriptions">
              <div>
                <b-btn
                  class="btn btn-md mt-1"
                  variant="primary"
                  @click="showRenewModal = true"
                  >Renew
                </b-btn>
                <img
                  v-b-popover.hover="
                    'This will override the scheduled time that the subscription renews automatically and renew it now. The customer will be charged and a new order will be created.'
                  "
                  title="Renew"
                  src="/images/store/popover.png"
                  class="popover-size"
                />
              </div>
              <div
                class="mt-2"
                v-if="!mealMixItems.isRunningLazy && !subscription.prepaid"
              >
                <router-link
                  :to="`/store/adjust-subscription/${subscription.id}`"
                >
                  <b-btn class="btn btn-warning btn-md">Adjust</b-btn>
                </router-link>
              </div>
              <div>
                <b-btn
                  v-if="
                    subscription.status === 'active' &&
                      !subscription.cancelled_at
                  "
                  class="btn btn-secondary btn-md mt-2"
                  @click.stop="() => pauseSubscription(subscription.id)"
                  >Pause</b-btn
                >
                <b-btn
                  v-if="
                    subscription.status === 'paused' &&
                      !subscription.cancelled_at
                  "
                  class="btn btn-secondary btn-md mt-2"
                  @click.stop="() => resumeSubscription(subscription.id)"
                  >Resume</b-btn
                >
              </div>
              <div>
                <button
                  :disabled="subscription.cancelled_at !== null"
                  class="btn btn-danger btn-md mt-2"
                  @click="showCancellationModal(subscription.id)"
                >
                  Cancel
                </button>
              </div>
            </span>
          </div>
          <div class="col-md-4">
            <h4>Placed On</h4>
            <p>{{ moment(subscription.created_at).format("dddd, MMM Do") }}</p>

            <span
              v-if="
                subscription.adjustedRenewal && !showingCancelledSubscriptions
              "
            >
              <h4 class="mt-2">Next Renewal</h4>
              <p v-if="subscription.status !== 'paused'">
                <i
                  v-if="!adjustingRenewal"
                  @click="adjustingRenewal = true"
                  class="fa fa-edit text-warning font-15 pt-1"
                ></i>
                <i
                  v-if="adjustingRenewal"
                  class="fas fa-check-circle text-primary pt-1 font-15"
                  @click="updateRenewalDate"
                ></i>
                <span v-if="!adjustingRenewal">
                  {{
                    moment(subscription.adjustedRenewal.date).format(
                      "dddd, MMM Do, h:mm a"
                    )
                  }}
                </span>
                <span v-else>
                  <b-select
                    placeholder="Renewal Date"
                    v-model="renewalDate"
                    :options="renewalDateOptions"
                    required
                  >
                    <option slot="top" disabled
                      >-- Select Renewal Day --</option
                    >
                  </b-select>
                  <b-select
                    placeholder="Renewal Time"
                    @input="val => formatRenewalTime(val)"
                    :options="renewalTimeOptions"
                    required
                  >
                    <option slot="top" disabled
                      >-- Select Renewal Time --</option
                    >
                  </b-select>
                </span>
                will create order for
                {{
                  moment(subscription.latest_unpaid_order_date).format(
                    "dddd, MMM Do"
                  )
                }}.
                <span
                  v-if="
                    subscription.prepaid &&
                      subscription.renewalCount !== 0 &&
                      subscription.renewalCount % subscription.prepaidWeeks !==
                        0
                  "
                >
                  This is a prepaid subscription. The customer will not be
                  charged this upcoming renewal.
                </span>
                <span
                  v-if="
                    subscription.prepaid &&
                      (subscription.renewalCount === 0 ||
                        subscription.renewalCount %
                          subscription.prepaidWeeks ===
                          0)
                  "
                >
                  This is a prepaid subscription. The customer will be charged
                  this upcoming renewal.
                </span>
                <span v-if="subscription.cancelled_at">
                  This subscription is marked for cancellation and will
                  automatically cancel after all prepaid orders are fulfilled.
                </span>
              </p>
              <p v-else>
                This subscription is currently paused and will not renew again
                until resumed.
              </p>
            </span>
            <h4 class="mt-2">Upcoming Delivery</h4>
            <p
              v-if="
                subscription.status !== 'cancelled' &&
                  subscription.next_delivery_date
              "
            >
              {{
                moment(subscription.next_delivery_date).format("dddd, MMM Do")
              }}
            </p>
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
              {{ subscription.transfer_type }} Fee:
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
            <p
              v-if="subscription.purchasedGiftCardReduction > 0"
              class="text-success"
            >
              Gift Card Discount: ({{
                format.money(
                  subscription.purchasedGiftCardReduction,
                  subscription.currency
                )
              }})
            </p>
            <p v-if="subscription.referralReduction > 0" class="text-success">
              Referral Discount: ({{
                format.money(
                  subscription.referralReduction,
                  subscription.currency
                )
              }})
            </p>
            <p v-if="subscription.promotionReduction > 0" class="text-success">
              Promotion Discount: ({{
                format.money(
                  subscription.promotionReduction,
                  subscription.currency
                )
              }})
            </p>
            <p v-if="subscription.pointsReduction > 0" class="text-success">
              Points Used: ({{
                format.money(
                  subscription.pointsReduction,
                  subscription.currency
                )
              }})
            </p>

            <p v-if="subscription.gratuity > 0">
              Gratuity:
              {{ format.money(subscription.gratuity, subscription.currency) }}
            </p>

            <p v-if="subscription.coolerDeposit > 0">
              Cooler Deposit:
              {{
                format.money(subscription.coolerDeposit, subscription.currency)
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
            <p v-if="user_detail.companyname">{{ user_detail.companyname }}</p>
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
              <h4>{{ subscription.transfer_type }} Day</h4>
              <span
                v-if="
                  subscription.status !== 'cancelled' &&
                    subscription.next_delivery_date
                "
              >
                {{
                  moment(subscription.next_delivery_date).format("dddd, MMM Do")
                }}
              </span>
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
        <div class="row">
          <div class="col-md-12" v-if="subscription.publicNotes">
            <h4>Notes</h4>
            <textarea
              type="text"
              id="form7"
              class="md-textarea form-control"
              rows="3"
              v-model="subscription.publicNotes"
              placeholder="Public notes sent to the customer in their emails and shown on your packing slips."
            ></textarea>
            <button
              class="btn btn-primary btn-md pull-right mt-2"
              @click="updateNotes(subscription.id)"
            >
              Update
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
              :options="optionsMeal"
              :columns="mealColumns"
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
                  {{ subscription.transfer_type }} Fee:
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
                <p v-if="subscription.gratuity > 0">
                  Gratuity:
                  {{
                    format.money(subscription.gratuity, subscription.currency)
                  }}
                </p>
                <p v-if="subscription.coolerDeposit > 0">
                  Cooler Deposit:
                  {{
                    format.money(
                      subscription.coolerDeposit,
                      subscription.currency
                    )
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
      cancelledSubscriptions: [],
      showingCancelledSubscriptions: false,
      email: "",
      showCancelModal: false,
      showRenewModal: false,
      renewalDate: null,
      renewalTime: null,
      deliveryDate: "All",
      filter: false,
      filters: {
        delivery_days: ["All"],
        notes: false
      },
      adjustingRenewal: false,
      viewSubscriptionModal: false,
      subscription: {},
      subscriptionId: "",
      user_detail: {},
      meals: {},
      columnsMeal: ["size", "quantity", "meal", "unit_price", "subtotal"],
      columnsMealMultipleDelivery: [
        "delivery_date",
        "size",
        "meal",
        "quantity",
        "unit_price",
        "subtotal"
      ],
      columns: [
        // "notes",
        "stripe_id",
        "interval",
        "user.user_detail.full_name",
        "user.user_detail.address",
        "user.user_detail.zip",
        // "user.user_detail.phone",
        "amount",
        "created_at",
        "next_delivery_date",
        "next_renewal",
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
          // "user.user_detail.phone": "Phone",
          amount: "Total",
          created_at: "Subscription Placed",
          next_delivery_date: "Upcoming Delivery",
          next_renewal: "Next Renewal",
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
      optionsMeal: {
        headings: {
          unit_price: "Unit Price",
          meal: "Item"
        },
        rowClassCallback: function(row) {
          let classes = `subscription-${row.id}`;
          classes += row.meal_package ? " strong" : "";
          return classes;
        }
      },
      deliveryNote: ""
    };
  },
  mounted() {
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
      getStoreMeal: "storeMeal",
      mealMixItems: "mealMixItems"
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
      let subscriptions = !this.showingCancelledSubscriptions
        ? this.subscriptions
        : this.cancelledSubscriptions;
      const subs = _.filter(subscriptions, subscription => {
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

      // const activeSubs = _.filter(subs, sub => {
      //   if (sub.status != "cancelled") {
      //     return true;
      //   }
      // });

      return subs;
    },
    mealColumns() {
      if (!this.subscription.isMultipleDelivery) {
        return this.columnsMeal;
      } else {
        return this.columnsMealMultipleDelivery;
      }
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
    },
    renewalDateOptions() {
      let options = [];
      var today = new Date();

      var year = today.getFullYear();
      var month = today.getMonth();
      var date = today.getDate();

      for (var i = 0; i < 30; i++) {
        var day = new Date(year, month, date + i);
        options.push({
          value: moment(day).format("YYYY-MM-DD"),
          text: moment(day).format("dddd MMM Do")
        });
      }
      return options;
    },
    renewalTimeOptions() {
      let options = [];

      for (let i = 0; i <= 23; i++) {
        options.push(moment(i, "hh").format("h:mm A"));
      }

      return options;
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
      // data = _.orderBy(data, "delivery_date");
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
    showCancellationModal(id) {
      this.subscriptionId = id;
      this.showCancelModal = true;
    },
    cancelSubscription() {
      let id = this.subscriptionId;
      axios.delete(`/api/me/subscriptions/${id}`).then(resp => {
        this.refreshTable();
        this.showCancelModal = false;
        this.viewSubscriptionModal = false;
        let sub = this.subscriptions.find(sub => {
          return sub.id === id;
        });
        if (sub.prepaid) {
          this.$toastr.s(
            "Subscription marked for cancellation after all prepaid orders are fulfilled"
          );
        } else {
          this.$toastr.s("Subscription cancelled.");
        }
      });
    },
    pauseSubscription(id) {
      try {
        axios.post("/api/me/subscriptions/pause", { id: id }).then(resp => {
          this.subscription = resp.data;
          this.refreshSubscriptions();
          this.$toastr.s("Subscription paused.");
        });
      } catch (e) {
        this.$toastr.w(
          "Please get in touch with our support team.",
          "Failed to pause Subscription"
        );
      }
    },
    resumeSubscription(id) {
      try {
        axios.post("/api/me/subscriptions/resume", { id: id }).then(resp => {
          this.subscription = resp.data;
          this.refreshSubscriptions();
          this.$toastr.s("Subscription resumed.");
        });
      } catch (e) {
        this.$toastr.w(
          "Please get in touch with our support team.",
          "Failed to resume Subscription"
        );
      }
    },
    adjust(id) {
      this.$router.push(`/store/adjust-subscription/${id}`);
    },
    renewModal(id) {
      this.viewSubscriptionModal = false;
      this.subscriptionId = id;
      this.showRenewModal = true;
    },
    renew() {
      axios
        .post("/api/me/subscriptions/renew", { id: this.subscriptionId })
        .then(resp => {
          this.subscription = resp.data;
          this.refreshSubscriptions();
          this.showRenewModal = false;
          if (!resp.data.failed_renewal) {
            this.$toastr.s("Subscription renewed.");
          } else {
            this.$toastr.w(
              "Subscription failed to renew. Reason: " +
                JSON.stringify(resp.data.failed_renewal_error)
            );
          }
        });
      // .catch(e => {
      //   this.$toastr.w(
      //     "Subscription failed to renew. Reason: " +
      //       JSON.stringify(e.response.data.message)
      //   );
      // });
    },
    updateRenewalDate() {
      if (!this.renewalDate) {
        this.$toastr.w("Please enter a renewal date.");
        return;
      }
      if (!this.renewalTime) {
        this.$toastr.w("Please enter a renewal time.");
        return;
      }
      axios
        .post("/api/me/subscriptions/updateRenewal", {
          id: this.subscriptionId,
          date: this.renewalDate,
          time: this.renewalTime ? this.renewalTime : null
        })
        .then(resp => {
          this.subscription = resp.data;
          this.refreshSubscriptions();
          this.adjustingRenewal = false;
          this.$toastr.s("Subscription renewal timing changed.");
        });
    },
    formatRenewalTime(time) {
      this.renewalTime = moment(time, "hh:mm a").format("HH:mm:ss");
    },
    testRunHourlyJob() {
      axios.get("/testRunHourlyJob");
    },
    updatedAfterRenewal(updatedDate) {
      let upcomingRenewal = moment(this.subscription.next_renewal_at);
      let lastRenewal = moment(
        upcomingRenewal
          .subtract(this.subscription.intervalCount, "weeks")
          .format("YYYY-MM-DD HH:MM:SS")
      );
      let updated = moment(updatedDate);

      if (updated.isSameOrAfter(lastRenewal)) {
        return true;
      }
    },
    async updateNotes(id) {
      axios
        .post("/api/me/updateSubNotes", {
          id: id,
          notes: this.subscription.publicNotes
        })
        .then(resp => {
          this.$toastr.s("Notes updated.");
        });
    },
    async showCancelledSubscriptions(val) {
      if (val) {
        axios.get("/api/me/cancelledSubscriptions").then(resp => {
          this.cancelledSubscriptions = resp.data;
          this.showingCancelledSubscriptions = true;
        });
      }
    },
    getMealTableData(subscription) {
      if (!this.initialized || !subscription.items) return [];

      let data = [];

      subscription.meal_package_items.forEach(meal_package_item => {
        if (meal_package_item.meal_package_size === null) {
          data.push({
            delivery_date: meal_package_item.delivery_date
              ? moment(meal_package_item.delivery_date).format("dddd, MMM Do")
              : null,
            size: meal_package_item.customSize
              ? meal_package_item.customSize
              : meal_package_item.meal_package.default_size_title,
            meal: meal_package_item.customTitle
              ? meal_package_item.customTitle
              : meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(
              meal_package_item.price,
              subscription.currency
            ),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              subscription.currency
            ),
            meal_package: true
          });
        } else {
          data.push({
            delivery_date: meal_package_item.delivery_date
              ? moment(meal_package_item.delivery_date).format("dddd, MMM Do")
              : null,
            size: meal_package_item.customSize
              ? meal_package_item.customSize
              : meal_package_item.meal_package_size.title,
            meal: meal_package_item.customTitle
              ? meal_package_item.customTitle
              : meal_package_item.meal_package.title,
            quantity: meal_package_item.quantity,
            unit_price: format.money(
              meal_package_item.price,
              subscription.currency
            ),
            subtotal: format.money(
              meal_package_item.price * meal_package_item.quantity,
              subscription.currency
            ),
            meal_package: true
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
              false,
              item.customTitle,
              item.customSize
            );

            data.push({
              delivery_date: item.delivery_date
                ? moment(item.delivery_date.date).format("dddd, MMM Do")
                : null,
              size: size ? size.title : meal.default_size_title,
              //meal: meal.title,
              meal: title,
              quantity: item.quantity,
              unit_price: "In Package",
              subtotal:
                item.added_price > 0
                  ? "In Package " +
                    "(" +
                    this.store.settings.currency_symbol +
                    item.added_price +
                    ")"
                  : "In Package"
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
          const size = item.customSize
            ? { title: item.customSize }
            : meal.getSize(item.meal_size_id);
          const title = item.customTitle
            ? item.customTitle
            : meal.getTitle(
                true,
                size,
                item.components,
                item.addons,
                item.special_instructions,
                false,
                item.customTitle,
                item.customSize
              );

          data.push({
            delivery_date: item.delivery_date
              ? moment(item.delivery_date.date).format("dddd, MMM Do")
              : null,
            size: size ? size.title : meal.default_size_title,
            //meal: meal.title,
            meal: title,
            quantity: item.quantity,
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
      data = _.orderBy(data, "delivery_date");
      return _.filter(data);
    }
  }
};
</script>
