f<template>
  <div class="main-customer-container box-shadow top-fill">
    <div class="row">
      <div class="col-md-12">
        <b-alert
          v-if="activeSubscriptions && activeSubscriptions[0]"
          :show="!!$route.query.created || false"
          variant="success"
        >
          <p class="center-text mt-3">
            Thank you for your order.
            <span v-if="!storeModules.hideTransferOptions">
              <span v-if="!!$route.query.pickup"
                >You can pick up your order on</span
              >
              <span v-else>Your order will be delivered on</span>
              {{
                moment(activeSubscriptions[0].next_delivery_date).format(
                  "dddd, MMM Do, Y"
                ) || ""
              }}
            </span>
          </p>
        </b-alert>

        <b-alert
          v-if="activeSubscriptions && activeSubscriptions[0]"
          :show="!!$route.query.updated || false"
          variant="success"
        >
          <p class="center-text mt-3">
            You have successfully updated your subscription.
          </p>
        </b-alert>

        <b-alert
          :show="activeSubscriptions && activeSubscriptions.length === 0"
          variant="warning"
        >
          <p class="center-text mt-3">You have no active subscriptions.</p>
        </b-alert>

        <Spinner v-if="isLoading" />
        <v-client-table
          :columns="columns"
          :data="tableData"
          :options="options"
          v-show="!isLoading"
        >
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
              @click="cancelSubscription(props.row.id)"
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
            <router-link :to="`/customer/subscriptions/${props.row.id}`">
              <b-btn class="btn btn-success btn-sm">Edit</b-btn>
            </router-link>
          </div>

          <div slot="amount" slot-scope="props">
            <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
          </div>
        </v-client-table>
      </div>
    </div>

    <div class="modal-basic">
      <b-modal
        v-model="viewSubscriptionModal"
        v-if="viewSubscriptionModal"
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
            <span v-if="!storeModules.hideTransferOptions" class="mt-2">
              <h4 v-if="!subscription.pickup">Delivery Day</h4>
              <h4 v-if="subscription.pickup">Pickup Day</h4>
              {{
                moment(subscription.next_order.delivery_date).format(
                  "dddd, MMM Do"
                )
              }}
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
import { mapGetters, mapActions, mapMutations } from "vuex";
import format from "../../lib/format.js";
import Spinner from "../../components/Spinner";
import moment from "moment";
import store from "../../store";

export default {
  components: {
    Spinner
  },
  data() {
    return {
      subscription: null,
      viewSubscriptionModal: false,
      isLoading: false,
      filters: {
        delivery_days: ["All"]
      },
      columns: [
        "stripe_id",
        "interval",
        "store_name",
        "amount",
        "created_at",
        "delivery_day",
        "charge_day",
        "status",
        "actions"
      ],
      columnsMeal: ["size", "meal", "quantity", "unit_price", "subtotal"],
      options: {
        headings: {
          stripe_id: "Subscription #",
          interval: "Interval",
          amount: "Total",
          created_at: "Subscription Placed",
          delivery_day: "Delivery Day",
          charge_day: "Charge Day",
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
      }
    };
  },
  computed: {
    ...mapGetters({
      subscriptions: "subscriptions",
      storeSettings: "storeSettings",
      initialized: "initialized",
      getStoreMeal: "viewedStoreMeal",
      storeModules: "viewedStoreModules"
    }),
    activeSubscriptions() {
      if (this.subscriptions)
        return this.subscriptions.filter(
          subscription => subscription.status != "cancelled"
        );
    },
    tableData() {
      let filters = {};
      if (_.isArray(this.filters.delivery_days)) {
        filters.delivery_days = this.filters.delivery_days;
      }

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
    }
  },
  mounted() {},
  methods: {
    ...mapActions(["refreshSubscriptions", "addJob", "removeJob"]),
    ...mapMutations([
      "emptyBag",
      "addBagItems",
      "setBagMealPlan",
      "setBagCoupon"
    ]),
    getOrderTableData(subscription) {
      if (!subscription || !_.isArray(subscription.orders)) {
        return [];
      }

      return subscription.orders.map(order => {
        return {
          date: order.created_at,
          delivery_date: order.delivery_date,
          delivered: order.fulfilled ? "Yes" : "No",
          meals: order.meals
            .map(meal => {
              return meal.title + " x " + meal.pivot.quantity;
            })
            .join(", ")
        };
      });
    },
    getMealTableData() {
      let subscription = this.subscription;
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

      return _.filter(data);
    },
    async pauseSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription.id}/pause`
        );
        this.$toastr.s("Subscription paused.");
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to pause Subscription"
        );
      }

      this.refreshSubscriptions();
    },
    async resumeSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription.id}/resume`
        );
        this.$toastr.s("Subscription resumed.");
      } catch (e) {
        this.$toastr.e(e.response.data.error);
      }

      this.refreshSubscriptions();
    },
    async cancelSubscription(subscription) {
      try {
        const resp = await axios.delete(
          `/api/me/subscriptions/${subscription}`
        );
        this.$toastr.s("Subscription cancelled.");
      } catch (e) {
        this.$toastr.e(
          "Please get in touch with our support team.",
          "Failed to cancel Subscription"
        );
      }

      this.refreshSubscriptions();
    },
    getIntervalDays(subscription) {
      if (subscription.interval === "week") {
        return 7;
      }
      if (subscription.interval === "month") {
        return 30;
      }
    },
    async viewSubscription(id) {
      const jobId = await this.addJob();
      axios
        .get(`/api/me/subscriptions/${id}`)
        .then(response => {
          this.subscription = response.data;
          this.viewSubscriptionModal = true;
        })
        .finally(() => {
          this.removeJob(jobId);
        });
    },
    formatMoney: format.money
  }
};
</script>
