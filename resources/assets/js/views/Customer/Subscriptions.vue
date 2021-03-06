<template>
  <div class="main-customer-container box-shadow top-fill">
    <div class="row">
      <div class="col-md-12">
        <b-alert
          v-if="activeSubscriptions && activeSubscriptions[0]"
          :show="!!$route.query.created || false"
          variant="success"
        >
          <p class="center-text mt-3">
            Thank you for your subscription.
            <span v-if="!storeModules.hideTransferOptions">
              <span v-if="$route.query.pickup"
                >You can pick up your order on</span
              >
              <span v-else>Your order will be delivered on</span>
              {{
                moment(activeSubscriptions[0].next_delivery_date).format(
                  "dddd, MMM Do, Y"
                ) || ""
              }}
              <span v-if="!$route.query.pickup">to {{ customerAddress }}.</span>
              <p v-if="!$route.query.pickup">
                If you'd like your order delivered to a different address,
                please change it
                <router-link :to="'/customer/account/my-account'"
                  >here
                </router-link>
                and we will deliver to the updated address.
              </p>
            </span>
          </p>
        </b-alert>

        <b-alert
          :show="!!$route.query.order || false"
          variant="primary"
          class="pt-3"
        >
          <p class="center-text mt-3">
            You also placed one time order. You can view that order
            <router-link :to="'/customer/orders'">here.</router-link>
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

        <Spinner v-if="!subscriptions" />
        <v-client-table :columns="columns" :data="tableData" :options="options">
          <div slot="interval" class="text-nowrap" slot-scope="props">
            {{ props.row.interval_title }}
          </div>
          <div
            slot="created_at"
            class="text-nowrap"
            slot-scope="props"
            v-if="storeSettings.timezone"
          >
            <span v-if="props.row.adjustedRenewal">{{
              moment(props.row.created_at).format("dddd, MMM Do")
            }}</span>
          </div>
          <div
            slot="next_renewal"
            class="text-nowrap"
            slot-scope="props"
            v-if="storeSettings.timezone"
          >
            <span v-if="props.row.adjustedRenewal">{{
              moment(props.row.adjustedRenewal.date).format("dddd, MMM Do")
            }}</span>
          </div>
          <div slot="delivery_day" class="text-nowrap" slot-scope="props">
            <span v-if="props.row.next_delivery_date"
              >{{ moment(props.row.next_delivery_date).format("dddd, MMM Do") }}
            </span>
          </div>
          <div slot="status" class="text-nowrap" slot-scope="props">
            {{
              props.row.status.charAt(0).toUpperCase() +
                props.row.status.slice(1)
            }}
            <span v-if="props.row.failed_renewal">
              <img
                v-b-popover.hover="
                  'Subscription failed to renew. Please fix the issue and then manually renew the subscription. Error: ' +
                    props.row.failed_renewal_error
                "
                title="Failed Renewal"
                src="/images/store/caution.png"
                class="pb-1"
              />
              <b-btn variant="dark" size="sm" @click="renew(props.row.id)"
                >Renew</b-btn
              >
            </span>
          </div>
          <div slot="actions" class="text-nowrap" slot-scope="props">
            <button
              class="btn view btn-primary btn-sm"
              @click="viewSubscription(props.row.id), (subId = props.row.id)"
            >
              View
            </button>
            <router-link
              v-if="!props.row.prepaid"
              :to="
                `/customer/adjust-subscription/${props.row.id}` +
                  '?subscriptionId=' +
                  props.row.id
              "
            >
              <b-btn class="btn btn-success btn-sm">Adjust</b-btn>
            </router-link>
            <button
              v-if="
                props.row.paid_order_count >= storeSettings.minimumSubWeeks ||
                  (props.row.intervalCount === 4 &&
                    props.row.paid_order_count > 0)
              "
              class="btn btn-danger btn-sm"
              :disabled="props.row.cancelled_at !== null"
              @click="
                {
                  (cancelSubscriptionModal = true),
                    (subId = props.row.id),
                    (subscription = props.row);
                }
              "
            >
              Cancel
            </button>
            <b-btn
              v-if="props.row.status === 'active' && !props.row.cancelled_at"
              class="btn btn-warning btn-sm"
              @click.stop="() => pauseSubscription(props.row.id)"
              >Pause</b-btn
            >
            <b-btn
              v-if="props.row.status === 'paused' && !props.row.cancelled_at"
              class="btn btn-warning btn-sm"
              @click.stop="() => resumeSubscription(props.row.id)"
              >Resume</b-btn
            >
            <b-btn
              variant="dark"
              class="btn btn-sm"
              v-if="
                (store.id === 3 || store.id === 106) &&
                  props.row.next_renewal_at.toString() !==
                    store.next_cutoff_date.toString() &&
                  !todayCutoff
              "
              @click="(showRenewModal = true), (subscription = props.row)"
              >Renew Early</b-btn
            >
            <img
              v-b-popover.hover="
                'This expedites your renewal to receive your order on the next possible delivery day.'
              "
              title="Renew Early"
              src="/images/store/popover.png"
              class="popover-size"
            />
          </div>

          <div slot="amount" slot-scope="props">
            <div>{{ formatMoney(props.row.amount, props.row.currency) }}</div>
          </div>
        </v-client-table>
      </div>
    </div>

    <b-modal
      size="md"
      title="Renew Subscription"
      v-model="showRenewModal"
      v-if="showRenewModal"
      hide-footer
    >
      <div class="center-text">
        <p v-if="store.settings.subscriptionRenewalType === 'now'" class="pt-3">
          This will renew the subscription now instead of the next scheduled
          renewal on
          {{ moment(subscription.next_renewal_at).format("dddd, MMM Do") }}. You
          will be charged right away and a new order will be created.
        </p>
        <p v-else class="pt-3">
          This will change the subscription renewal day from the next scheduled
          renewal on
          {{ moment(subscription.next_renewal_at).format("dddd, MMM Do") }} to
          the most upcoming renewal date on
          {{ moment(store.next_cutoff_date).format("dddd, MMM Do") }}.
        </p>

        <p class="strong">Proceed?</p>

        <b-btn class="btn btn-md" variant="primary" @click="expediteRenewal()"
          >Renew Early</b-btn
        >
      </div>
    </b-modal>

    <b-modal
      size="md"
      title="Cancel Subscription"
      v-model="cancelSubscriptionModal"
      v-if="cancelSubscriptionModal"
      hide-footer
    >
      <p class="center-text mt-3 mb-3">
        Are you sure you want to cancel your subscription?
        <span v-if="!subscription.prepaid"
          >If you want to change your items you can click "Adjust" instead to
          edit this subscription.</span
        >
      </p>
      <center>
        <b-btn variant="danger" @click="cancelSubscription">Cancel</b-btn>
        <router-link
          v-if="!subscription.prepaid"
          :to="
            `/customer/adjust-subscription/${subId}` +
              '?subscriptionId=' +
              subId
          "
        >
          <b-btn class="btn btn-success btn-md">Adjust</b-btn>
        </router-link>
      </center>
    </b-modal>

    <div class="modal-basic">
      <b-modal
        v-model="viewSubscriptionModal"
        v-if="viewSubscriptionModal"
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
                {{ subscription.store.details.name }} updated this subscription
                on
                {{ moment(subscription.store_updated).format("dddd, MMM Do") }}.
              </div>
              <div
                v-if="
                  subscription.customer_updated &&
                    updatedAfterRenewal(subscription.customer_updated)
                "
              >
                You updated this subscription on
                {{
                  moment(subscription.customer_updated).format("dddd, MMM Do")
                }}.
              </div>
            </b-alert>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-md-4">
            <h4>Subscription ID</h4>
            <p>{{ subscription.stripe_id }}</p>
            <div class="mt-3" v-if="subscription.publicNotes">
              <h4>Notes</h4>
              <p>{{ subscription.publicNotes }}</p>
            </div>
            <router-link
              v-if="!subscription.prepaid"
              :to="`/customer/adjust-subscription/${subscription.id}`"
            >
              <b-btn class="btn btn-success btn-sm">Adjust</b-btn>
            </router-link>
            <b-btn
              v-if="
                subscription.status === 'active' && !subscription.cancelled_at
              "
              class="btn btn-warning btn-sm"
              @click.stop="() => pauseSubscription(subscription.id)"
              >Pause</b-btn
            >
            <b-btn
              v-if="
                subscription.status === 'paused' && !subscription.cancelled_at
              "
              class="btn btn-warning btn-sm"
              @click.stop="() => resumeSubscription(subscription.id)"
              >Resume</b-btn
            >
            <button
              v-if="
                subscription.paid_order_count >=
                  storeSettings.minimumSubWeeks ||
                  (subscription.intervalCount === 4 &&
                    subscription.paid_order_count > 0)
              "
              class="btn btn-danger btn-sm"
              @click="cancelSubscription"
            >
              Cancel
            </button>

            <b-btn
              variant="dark"
              class="btn btn-sm"
              v-if="
                (store.id === 3 || store.id === 106) &&
                  subscription.next_renewal_at.toString() !==
                    store.next_cutoff_date.toString()
              "
              @click="showRenewModal = true"
              >Renew Early</b-btn
            >
            <img
              v-b-popover.hover="
                'This expedites your renewal to receive your order on the next possible delivery day.'
              "
              title="Renew Early"
              src="/images/store/popover.png"
              class="popover-size"
            />
          </div>
          <div class="col-md-4">
            <h4>Placed On</h4>
            <p>{{ moment(subscription.created_at).format("dddd, MMM Do") }}</p>
            <span>
              <h4 class="mt-2">Next Renewal</h4>
              <p v-if="subscription.status !== 'paused'">
                {{
                  moment(subscription.adjustedRenewal.date).format(
                    "dddd, MMM Do, h:mm a"
                  )
                }}
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
                  This is a prepaid subscription. You will not be charged this
                  upcoming renewal.
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
                  This is a prepaid subscription. You will be charged this
                  upcoming renewal.
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
            <span v-if="!storeModules.hideTransferOptions" class="mt-2">
              <h4>Upcoming {{ subscription.transfer_type }}</h4>
              <p v-if="subscription.next_delivery_date">
                {{
                  moment(subscription.next_delivery_date).format("dddd, MMM Do")
                }}
              </p>
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
              :columns="mealColumns"
              :options="optionsMeal"
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
      showRenewModal: false,
      subscription: null,
      viewSubscriptionModal: false,
      isLoading: false,
      subId: null,
      cancelSubscriptionModal: false,
      filters: {
        delivery_days: ["All"]
      },
      columns: [
        "stripe_id",
        "interval",
        // "store_name",
        "amount",
        "created_at",
        "delivery_day",
        "next_renewal",
        "status",
        "actions"
      ],
      columnsMeal: ["size", "meal", "quantity", "unit_price", "subtotal"],
      columnsMealMultipleDelivery: [
        "delivery_date",
        "size",
        "meal",
        "quantity",
        "unit_price",
        "subtotal"
      ],
      options: {
        headings: {
          stripe_id: "Subscription #",
          interval: "Interval",
          amount: "Total",
          created_at: "Subscription Placed",
          delivery_day: "Upcoming Delivery",
          next_renewal: "Next Renewal",
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
      }
    };
  },
  computed: {
    ...mapGetters({
      subscriptions: "subscriptions",
      storeSettings: "storeSettings",
      initialized: "initialized",
      getStoreMeal: "viewedStoreMeal",
      storeModules: "viewedStoreModules",
      user: "user",
      store: "viewedStore"
    }),
    todayCutoff() {
      if (
        moment(this.store.next_cutoff_date).format("dddd, MMM Do") ===
        moment().format("dddd, MMM Do")
      ) {
        return true;
      } else {
        return false;
      }
    },
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
    },
    mealColumns() {
      if (!this.subscription.isMultipleDelivery) {
        return this.columnsMeal;
      } else {
        return this.columnsMealMultipleDelivery;
      }
    },
    customerAddress() {
      let detail = this.user.user_detail;
      return (
        detail.address +
        ", " +
        detail.city +
        ", " +
        detail.state +
        " " +
        detail.zip
      );
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
          date: order.paid_at,
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

      let meal_package_items = _.orderBy(
        subscription.meal_package_items,
        "delivery_date"
      );
      let items = _.orderBy(subscription.items, "delivery_date");

      meal_package_items.forEach(meal_package_item => {
        if (meal_package_item.meal_package_size === null) {
          data.push({
            delivery_date: meal_package_item.delivery_date
              ? moment(meal_package_item.delivery_date).format("dddd, MMM Do")
              : null,
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
            ),
            meal_package: true
          });
        } else {
          data.push({
            delivery_date: meal_package_item.delivery_date
              ? moment(meal_package_item.delivery_date).format("dddd, MMM Do")
              : null,
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
            ),
            meal_package: true
          });
        }

        items.forEach(item => {
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

      items.forEach(item => {
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
      // data = _.orderBy(data, "delivery_date");
      return _.filter(data);
    },
    async pauseSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription}/pause`
        );
        this.$toastr.s("Subscription paused.");
      } catch (e) {
        this.$toastr.w(
          "Please get in touch with our support team.",
          "Failed to pause Subscription"
        );
      }

      this.refreshSubscriptions();
    },
    async resumeSubscription(subscription) {
      try {
        const resp = await axios.post(
          `/api/me/subscriptions/${subscription}/resume`
        );
        this.$toastr.s("Subscription resumed.");
      } catch (e) {
        this.$toastr.w(e.response.data.error);
      }

      this.refreshSubscriptions();
    },
    async cancelSubscription() {
      try {
        const resp = await axios.delete(`/api/me/subscriptions/${this.subId}`);
        let sub = this.subscriptions.find(sub => {
          return sub.id === this.subId;
        });
        if (sub.prepaid) {
          this.$toastr.s(
            "Subscription marked for cancellation after all prepaid orders are fulfilled"
          );
        } else {
          this.$toastr.s("Subscription cancelled.");
        }
      } catch (e) {
        this.$toastr.w(
          "Please get in touch with our support team.",
          "Failed to cancel Subscription"
        );
      }
      this.cancelSubscriptionModal = false;
      this.viewSubscriptionModal = false;
      this.refreshSubscriptions();
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
    getIntervalDays(subscription) {
      if (subscription.interval === "week") {
        return 7;
      }
      if (subscription.interval === "month") {
        return 30;
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
    expediteRenewal() {
      axios
        .post("/api/me/expediteRenewal", { subId: this.subscription.id })
        .then(resp => {
          this.refreshSubscriptions();
          this.showRenewModal = false;
          if (this.store.settings.subscriptionRenewalType === "now") {
            this.$toastr.s("Subscription renewed successfully");
          } else {
            this.$toastr.s("Subscription renewal day successfully moved.");
          }
        });
    },
    renew(subId) {
      axios.post("/api/me/renewSubscription", { id: subId }).then(resp => {
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
    },
    formatMoney: format.money
  }
};
</script>
